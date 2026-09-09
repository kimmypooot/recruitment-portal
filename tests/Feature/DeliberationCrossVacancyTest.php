<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\HrmpsbComposition;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * DeliberationController::decide() and AppointingAuthorityController::decide()
 * take a route-bound $vacancy and a caller-supplied application_id, validated
 * only for existence — not that the application actually belongs to that
 * vacancy. Without the fix, a decision for vacancy B's application could be
 * recorded under vacancy A's route, corrupting the decision record's
 * vacancy_id and — on "appointed" — flipping the wrong vacancy to "filled".
 */
class DeliberationCrossVacancyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Vacancy $vacancyA;

    private Vacancy $vacancyB;

    private Application $applicationInB;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        $this->admin = User::factory()->admin()->create();
        $this->vacancyA = Vacancy::factory()->published()->create();
        $this->vacancyB = Vacancy::factory()->published()->create();

        $profile = ApplicantProfile::factory()->complete()->create();
        $this->applicationInB = Application::create([
            'vacancy_id' => $this->vacancyB->id,
            'applicant_id' => $profile->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    private function chairFor(): User
    {
        $user = User::factory()->hrmpsb()->create();
        HrmpsbComposition::create([
            'user_id' => $user->id,
            'hrmpsb_role' => 'chairperson',
            'is_active' => true,
            'assigned_by' => $this->admin->id,
        ]);

        return $user;
    }

    private function appointingAuthority(): User
    {
        $user = User::factory()->hrmpsb()->create();
        HrmpsbComposition::create([
            'user_id' => $user->id,
            'hrmpsb_role' => 'appointing-authority',
            'is_active' => true,
            'assigned_by' => $this->admin->id,
        ]);

        return $user;
    }

    public function test_deliberation_decide_rejects_an_application_from_a_different_vacancy(): void
    {
        $chair = $this->chairFor();

        $response = $this->actingAs($chair, 'sanctum')
            ->postJson("/api/deliberation/{$this->vacancyA->id}/decide", [
                'application_id' => $this->applicationInB->id,
                'action' => 'appointed',
            ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('deliberation_results', [
            'vacancy_id' => $this->vacancyA->id,
            'application_id' => $this->applicationInB->id,
        ]);
        // The application and its real vacancy must be untouched.
        $this->assertDatabaseHas('applications', ['id' => $this->applicationInB->id, 'status' => 'submitted']);
        $this->assertDatabaseHas('vacancies', ['id' => $this->vacancyA->id, 'status' => 'published']);
    }

    public function test_deliberation_decide_still_works_for_the_application_s_own_vacancy(): void
    {
        $chair = $this->chairFor();

        $this->actingAs($chair, 'sanctum')
            ->postJson("/api/deliberation/{$this->vacancyB->id}/decide", [
                'application_id' => $this->applicationInB->id,
                'action' => 'endorsed',
            ])
            ->assertCreated();
    }

    public function test_appointing_authority_decide_rejects_an_application_from_a_different_vacancy(): void
    {
        $aa = $this->appointingAuthority();

        $response = $this->actingAs($aa, 'sanctum')
            ->patchJson("/api/deliberation/{$this->vacancyA->id}/appointing-authority/decide", [
                'application_id' => $this->applicationInB->id,
                'action' => 'appointed',
            ]);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing('appointing_authority_decisions', [
            'vacancy_id' => $this->vacancyA->id,
            'application_id' => $this->applicationInB->id,
        ]);
        $this->assertDatabaseHas('applications', ['id' => $this->applicationInB->id, 'status' => 'submitted']);
        // The wrong vacancy must not have been flipped to "filled".
        $this->assertDatabaseHas('vacancies', ['id' => $this->vacancyA->id, 'status' => 'published']);
    }

    public function test_appointing_authority_decide_still_works_for_the_application_s_own_vacancy(): void
    {
        $aa = $this->appointingAuthority();

        $this->actingAs($aa, 'sanctum')
            ->patchJson("/api/deliberation/{$this->vacancyB->id}/appointing-authority/decide", [
                'application_id' => $this->applicationInB->id,
                'action' => 'appointed',
            ])
            ->assertOk();

        $this->assertDatabaseHas('vacancies', ['id' => $this->vacancyB->id, 'status' => 'filled']);
    }
}
