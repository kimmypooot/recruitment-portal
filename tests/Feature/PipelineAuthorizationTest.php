<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\BeiRating;
use App\Models\CbweRating;
use App\Models\Competency;
use App\Models\ExamResult;
use App\Models\HrmpsbComposition;
use App\Models\QsEvaluation;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyCompetency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression coverage for the audit's Phase 2 item "zero authorization test
 * coverage across the evaluation pipeline". Exercises the two authorization
 * layers each of these routes actually has: the role:hrmpsb,admin route
 * middleware (blocks applicants outright) and each controller's own active-
 * HrmpsbComposition/role check (blocks an hrmpsb account with no board seat,
 * or the wrong seat, even though the route middleware alone would let them
 * through).
 */
class PipelineAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Vacancy $vacancy;
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        // See EvaluationPolicyTest — SQLite's inherited CHECK constraint from
        // the original users.role enum doesn't include 'hrmpsb'.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        $this->admin = User::factory()->admin()->create();
        $this->vacancy = Vacancy::factory()->published()->create();

        $profile = ApplicantProfile::factory()->complete()->create();
        $this->application = Application::create([
            'vacancy_id'   => $this->vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        // CBWE's competency-key validation intersects the vacancy's assigned
        // competencies with CbweRating::COMPETENCY_KEYS; BEI's validation
        // just checks against whatever's assigned. One assigned key covers
        // both.
        Competency::firstOrCreate(
            ['competency_key' => 'exemplifying_integrity'],
            ['competency_name' => 'Exemplifying Integrity', 'competency_group' => 'Core', 'sort_order' => 1]
        );
        VacancyCompetency::create([
            'vacancy_id'     => $this->vacancy->id,
            'competency_key' => 'exemplifying_integrity',
            'competency_level' => 1,
        ]);
    }

    private function validCompetencyScores(): array
    {
        return ['exemplifying_integrity' => 3];
    }

    private function hrmpsbUser(?string $compositionRole = null): User
    {
        $user = User::factory()->hrmpsb()->create();

        if ($compositionRole !== null) {
            HrmpsbComposition::create([
                'user_id'     => $user->id,
                'hrmpsb_role' => $compositionRole,
                'is_active'   => true,
                'assigned_by' => $this->admin->id,
            ]);
        }

        return $user;
    }

    /** Locks a majority-qualified QS result for $this->application. */
    private function passQsStage(): void
    {
        QsEvaluation::create([
            'application_id'    => $this->application->id,
            'evaluator_id'      => $this->hrmpsbUser('secretariat')->id,
            'education_meets'   => true,
            'experience_meets'  => true,
            'training_meets'    => true,
            'eligibility_meets' => true,
            'overall_qualified' => true,
            'evaluated_at'      => now(),
            'locked_at'         => now(),
        ]);
    }

    /** Passes TWE and locks a CBWE rating, satisfying BEI's prerequisites. */
    private function passCbweStage(): void
    {
        ExamResult::create([
            'application_id' => $this->application->id,
            'exam_type'      => 'TWE',
            'raw_score'      => 90,
            'max_score'      => 100,
            'encoded_by'     => $this->admin->id,
            'encoded_at'     => now(),
        ]);

        CbweRating::create([
            'application_id'    => $this->application->id,
            'evaluator_id'      => $this->hrmpsbUser('secretariat')->id,
            'competency_scores' => $this->validCompetencyScores(),
            'rated_at'          => now(),
            'locked_at'         => now(),
        ]);
    }

    /** Locks a BEI rating, satisfying EOPT's prerequisite. */
    private function passBeiStage(): void
    {
        BeiRating::create([
            'application_id'    => $this->application->id,
            'evaluator_id'      => $this->hrmpsbUser('secretariat')->id,
            'competency_scores' => $this->validCompetencyScores(),
            'rated_at'          => now(),
            'locked_at'         => now(),
        ]);
    }

    // ── Route-level: applicants are blocked outright ────────────────────────

    public function test_applicant_is_blocked_from_every_pipeline_route(): void
    {
        $applicant = User::factory()->applicant()->create();
        $v = $this->vacancy->id;

        $routes = [
            ['get', "/api/qs-evaluations/{$v}"],
            ['post', '/api/qs-evaluations'],
            ['get', "/api/cbwe-ratings/{$v}"],
            ['post', '/api/cbwe-ratings'],
            ['get', "/api/bei-ratings/{$v}"],
            ['post', '/api/bei-ratings'],
            ['get', "/api/eopt/{$v}"],
            ['post', "/api/eopt/{$v}"],
            ['get', "/api/background-checks/{$v}"],
            ['post', '/api/background-checks'],
            ['get', "/api/deliberation/{$v}"],
            ['patch', "/api/deliberation/{$v}/unmask"],
            ['post', "/api/deliberation/{$v}/decide"],
            ['patch', "/api/deliberation/{$v}/lock"],
            ['get', "/api/deliberation/{$v}/appointing-authority"],
            ['patch', "/api/deliberation/{$v}/appointing-authority/decide"],
        ];

        foreach ($routes as [$method, $uri]) {
            $this->actingAs($applicant, 'sanctum')
                ->json($method, $uri, [])
                ->assertForbidden();
        }
    }

    // ── Controller-level: an hrmpsb account with no board seat is blocked ──

    public function test_hrmpsb_without_a_composition_cannot_submit_qs_evaluation(): void
    {
        $noSeat = $this->hrmpsbUser();

        $this->actingAs($noSeat, 'sanctum')
            ->postJson('/api/qs-evaluations', [
                'application_id'    => $this->application->id,
                'education_meets'   => true,
                'experience_meets'  => true,
                'training_meets'    => true,
                'eligibility_meets' => true,
            ])
            ->assertForbidden();
    }

    public function test_hrmpsb_without_a_composition_cannot_submit_cbwe_rating(): void
    {
        $this->passQsStage();
        $noSeat = $this->hrmpsbUser();

        $this->actingAs($noSeat, 'sanctum')
            ->postJson('/api/cbwe-ratings', [
                'application_id'     => $this->application->id,
                'competency_scores'  => $this->validCompetencyScores(),
            ])
            ->assertForbidden();
    }

    public function test_chairperson_can_submit_cbwe_rating_once_qs_is_passed(): void
    {
        $this->passQsStage();
        $chair = $this->hrmpsbUser('chairperson');

        $this->actingAs($chair, 'sanctum')
            ->postJson('/api/cbwe-ratings', [
                'application_id'    => $this->application->id,
                'competency_scores' => $this->validCompetencyScores(),
            ])
            ->assertCreated();
    }

    public function test_hrmpsb_without_a_composition_cannot_submit_bei_rating(): void
    {
        $this->passQsStage();
        $this->passCbweStage();
        $noSeat = $this->hrmpsbUser();

        $this->actingAs($noSeat, 'sanctum')
            ->postJson('/api/bei-ratings', [
                'application_id'    => $this->application->id,
                'competency_scores' => $this->validCompetencyScores(),
            ])
            ->assertForbidden();
    }

    public function test_eopt_requires_secretariat_or_admin_not_just_any_composition(): void
    {
        $this->passQsStage();
        $this->passCbweStage();
        $this->passBeiStage();

        // A plain board member (chairperson) is an active HRMPSB composition
        // holder but EoptController::store specifically requires secretariat
        // or admin — a weaker "any active member" check here would wrongly
        // let this request through.
        $chair = $this->hrmpsbUser('chairperson');

        $this->actingAs($chair, 'sanctum')
            ->postJson("/api/eopt/{$this->vacancy->id}", [
                'application_id'          => $this->application->id,
                'emotional_stability'     => 'high',
                'extraversion'            => 'high',
                'openness_to_experience'  => 'high',
                'agreeableness'           => 'high',
                'conscientiousness'       => 'high',
            ])
            ->assertForbidden();

        $secretariat = $this->hrmpsbUser('secretariat');

        $this->actingAs($secretariat, 'sanctum')
            ->postJson("/api/eopt/{$this->vacancy->id}", [
                'application_id'          => $this->application->id,
                'emotional_stability'     => 'high',
                'extraversion'            => 'high',
                'openness_to_experience'  => 'high',
                'agreeableness'           => 'high',
                'conscientiousness'       => 'high',
            ])
            ->assertCreated();
    }

    public function test_hrmpsb_without_a_composition_cannot_submit_background_check(): void
    {
        $noSeat = $this->hrmpsbUser();

        $this->actingAs($noSeat, 'sanctum')
            ->postJson('/api/background-checks', [
                'application_id'      => $this->application->id,
                'employment_verified' => true,
            ])
            ->assertForbidden();
    }

    public function test_deliberation_decide_requires_chairperson_or_above(): void
    {
        // pintig-representative-1st is a real composition role but not one of
        // the roles DeliberationController::decide() grants decision power
        // to — a bare "is this user an active board member" check would
        // wrongly allow this.
        $member = $this->hrmpsbUser('pintig-representative-1st');

        $this->actingAs($member, 'sanctum')
            ->postJson("/api/deliberation/{$this->vacancy->id}/decide", [
                'application_id' => $this->application->id,
                'action'         => 'endorsed',
            ])
            ->assertForbidden();

        $chair = $this->hrmpsbUser('chairperson');

        $this->actingAs($chair, 'sanctum')
            ->postJson("/api/deliberation/{$this->vacancy->id}/decide", [
                'application_id' => $this->application->id,
                'action'         => 'endorsed',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('applications', [
            'id'     => $this->application->id,
            'status' => 'recommended',
        ]);
    }

    public function test_only_an_admin_module_user_can_unmask_identities(): void
    {
        // A chairperson can decide but cannot unmask — that's deliberately
        // restricted to canAccessAdminModule() (admin, or hrmpsb + an
        // active secretariat/hr-chief designation).
        $chair = $this->hrmpsbUser('chairperson');

        $this->actingAs($chair, 'sanctum')
            ->patchJson("/api/deliberation/{$this->vacancy->id}/unmask")
            ->assertForbidden();

        $secretariat = $this->hrmpsbUser('secretariat');

        $this->actingAs($secretariat, 'sanctum')
            ->patchJson("/api/deliberation/{$this->vacancy->id}/unmask")
            ->assertOk();
    }

    public function test_appointing_authority_decide_requires_the_right_composition_role(): void
    {
        $member = $this->hrmpsbUser('pintig-representative-1st');

        $this->actingAs($member, 'sanctum')
            ->patchJson("/api/deliberation/{$this->vacancy->id}/appointing-authority/decide", [
                'application_id' => $this->application->id,
                'action'         => 'appointed',
            ])
            ->assertForbidden();

        $aa = $this->hrmpsbUser('appointing-authority');

        $this->actingAs($aa, 'sanctum')
            ->patchJson("/api/deliberation/{$this->vacancy->id}/appointing-authority/decide", [
                'application_id' => $this->application->id,
                'action'         => 'appointed',
            ])
            ->assertOk();

        $this->assertDatabaseHas('applications', [
            'id'     => $this->application->id,
            'status' => 'appointed',
        ]);
        $this->assertDatabaseHas('vacancies', [
            'id'     => $this->vacancy->id,
            'status' => 'filled',
        ]);
    }

    // ── User management: role escalation boundary ───────────────────────────

    public function test_hrmpsb_user_without_admin_designation_cannot_manage_users(): void
    {
        $noSeat = $this->hrmpsbUser();

        $this->actingAs($noSeat, 'sanctum')
            ->getJson('/api/admin/users')
            ->assertForbidden();

        $this->actingAs($noSeat, 'sanctum')
            ->postJson('/api/admin/users', [
                'first_name'            => 'Test',
                'last_name'             => 'User',
                'email'                 => 'newuser@example.test',
                'password'              => 'Password123',
                'password_confirmation' => 'Password123',
                'role'                  => 'admin',
            ])
            ->assertForbidden();
    }

    public function test_hrmpsb_secretariat_designation_grants_user_management(): void
    {
        $secretariat = $this->hrmpsbUser('secretariat');

        $this->actingAs($secretariat, 'sanctum')
            ->getJson('/api/admin/users')
            ->assertOk();
    }

    public function test_user_creation_rejects_a_role_outside_the_allowed_set(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/users', [
                'first_name'            => 'Test',
                'last_name'             => 'User',
                'email'                 => 'newuser2@example.test',
                'password'              => 'Password123',
                'password_confirmation' => 'Password123',
                'role'                  => 'super-admin',
            ])
            ->assertUnprocessable();
    }

    // ── updateStatus: audit logging and access boundary ─────────────────────

    public function test_hrmpsb_without_admin_designation_cannot_use_the_generic_status_endpoint(): void
    {
        $noSeat = $this->hrmpsbUser();

        $this->actingAs($noSeat, 'sanctum')
            ->patchJson("/api/applications/{$this->application->id}/status", [
                'status' => 'under_review',
            ])
            ->assertForbidden();
    }

    public function test_updating_status_writes_an_audit_log_entry(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/applications/{$this->application->id}/status", [
                'status' => 'under_review',
            ])
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action'          => 'application_status_updated',
            'auditable_type'  => Application::class,
            'auditable_id'    => $this->application->id,
            'user_id'         => $this->admin->id,
        ]);
    }
}
