<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\HrmpsbComposition;
use App\Models\QsEvaluation;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression coverage for the production-readiness audit's Phase 1 fixes:
 * the duplicate-application race, the post-lock evaluation reopening gap,
 * and withdrawn applications remaining scoreable.
 */
class ApplicationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // See EvaluationPolicyTest — SQLite's inherited CHECK constraint from
        // the original users.role enum doesn't include 'hrmpsb'.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }
    }

    // ── Duplicate-application DB constraint ─────────────────────────────────

    public function test_database_rejects_a_duplicate_active_application_bypassing_the_controller(): void
    {
        $profile = ApplicantProfile::factory()->complete()->create();
        $vacancy = Vacancy::factory()->published()->create();

        Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        // A second insert for the same (vacancy, applicant) skipping the
        // controller's own existence check entirely — this is exactly the
        // shape of the race two concurrent HTTP requests would hit. The
        // applications_active_vacancy_applicant_unique constraint must reject
        // it at the database layer, not just the application layer.
        $this->expectException(QueryException::class);

        Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function test_reapplying_after_a_soft_deleted_application_is_still_allowed(): void
    {
        $profile = ApplicantProfile::factory()->complete()->create();
        $vacancy = Vacancy::factory()->published()->create();

        $first = Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);
        $first->delete(); // soft delete

        $second = Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->assertNotNull($second->id);
        $this->assertDatabaseCount('applications', 2);
    }

    // ── Withdrawn applications can no longer be scored ──────────────────────

    public function test_qs_evaluation_is_rejected_for_a_withdrawn_application(): void
    {
        $admin   = User::factory()->admin()->create();
        $member  = User::factory()->hrmpsb()->create();
        HrmpsbComposition::create([
            'user_id'     => $member->id,
            'hrmpsb_role' => 'chairperson',
            'is_active'   => true,
            'assigned_by' => $admin->id,
        ]);

        $profile = ApplicantProfile::factory()->complete()->create();
        $vacancy = Vacancy::factory()->published()->create();
        $application = Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'withdrawn',
            'submitted_at' => now(),
        ]);

        $this->actingAs($member, 'sanctum')
            ->postJson('/api/qs-evaluations', [
                'application_id'    => $application->id,
                'education_meets'   => true,
                'experience_meets'  => true,
                'training_meets'    => true,
                'eligibility_meets' => true,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('qs_evaluations', ['application_id' => $application->id]);
    }

    // ── Locked QS stage can't be reopened by a late-arriving evaluator ─────

    public function test_qs_evaluation_is_rejected_from_a_new_evaluator_after_the_stage_is_locked(): void
    {
        $admin = User::factory()->admin()->create();

        $secretariat = User::factory()->hrmpsb()->create();
        HrmpsbComposition::create([
            'user_id'     => $secretariat->id,
            'hrmpsb_role' => 'secretariat',
            'is_active'   => true,
            'assigned_by' => $admin->id,
        ]);

        $lateMember = User::factory()->hrmpsb()->create();
        HrmpsbComposition::create([
            'user_id'     => $lateMember->id,
            'hrmpsb_role' => 'chairperson',
            'is_active'   => true,
            'assigned_by' => $admin->id,
        ]);

        $profile = ApplicantProfile::factory()->complete()->create();
        $vacancy = Vacancy::factory()->published()->create();
        $application = Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        // Secretariat evaluates and the stage is locked before $lateMember
        // ever submits — so $lateMember has no existing QsEvaluation row.
        QsEvaluation::create([
            'application_id'    => $application->id,
            'evaluator_id'      => $secretariat->id,
            'education_meets'   => true,
            'experience_meets'  => true,
            'training_meets'    => true,
            'eligibility_meets' => true,
            'overall_qualified' => true,
            'evaluated_at'      => now(),
        ]);

        $this->actingAs($secretariat, 'sanctum')
            ->patchJson("/api/qs-evaluations/{$vacancy->id}/lock")
            ->assertOk();

        // $lateMember never submitted before the lock — without the stage-wide
        // check, $existing would be null and this would silently succeed.
        $this->actingAs($lateMember, 'sanctum')
            ->postJson('/api/qs-evaluations', [
                'application_id'    => $application->id,
                'education_meets'   => true,
                'experience_meets'  => true,
                'training_meets'    => true,
                'eligibility_meets' => true,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('qs_evaluations', [
            'application_id' => $application->id,
            'evaluator_id'   => $lateMember->id,
        ]);
    }
}
