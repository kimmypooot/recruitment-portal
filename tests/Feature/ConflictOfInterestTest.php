<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicantProfile;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConflictOfInterestTest extends TestCase
{
    use RefreshDatabase;

    private function applyAs(User $user, Vacancy $vacancy): Application
    {
        $profile = ApplicantProfile::factory()->create(['user_id' => $user->id]);

        return Application::create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $profile->id,
            'status' => 'submitted',
        ]);
    }

    public function test_evaluator_with_own_application_is_blocked_from_the_vacancy(): void
    {
        $member = User::factory()->hrmpsb()->create();
        $vacancy = Vacancy::factory()->published()->create();
        $this->applyAs($member, $vacancy);

        $this->actingAs($member, 'sanctum')->getJson("/api/qs-evaluations/{$vacancy->id}")->assertForbidden();
        $this->actingAs($member, 'sanctum')->getJson("/api/deliberation/{$vacancy->id}")->assertForbidden();
    }

    public function test_evaluator_is_blocked_from_rating_their_own_application_by_body_id(): void
    {
        $member = User::factory()->hrmpsb()->create();
        $application = $this->applyAs($member, Vacancy::factory()->published()->create());

        $this->actingAs($member, 'sanctum')
            ->postJson('/api/bei-ratings', ['application_id' => $application->id])
            ->assertForbidden()
            ->assertJsonPath('code', 'conflict_of_interest');
    }

    public function test_admin_with_own_application_cannot_change_its_status(): void
    {
        $admin = User::factory()->admin()->create();
        $application = $this->applyAs($admin, Vacancy::factory()->published()->create());

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/applications/{$application->id}/status", ['status' => 'qualified'])
            ->assertForbidden();
    }

    public function test_conflict_is_per_vacancy_not_global(): void
    {
        // Admin, so the controller's own board-seat check passes and only the conflict rule is in play.
        $member = User::factory()->admin()->create();
        $applied = Vacancy::factory()->published()->create();
        $other = Vacancy::factory()->published()->create();
        $this->applyAs($member, $applied);

        $this->actingAs($member, 'sanctum')->getJson("/api/qs-evaluations/{$applied->id}")->assertForbidden();
        $this->actingAs($member, 'sanctum')->getJson("/api/qs-evaluations/{$other->id}")->assertStatus(200);
    }

    public function test_declared_relative_recusal_blocks_evaluation_of_that_vacancy_only(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->admin()->create();
        $vacancy = Vacancy::factory()->published()->create();
        $other = Vacancy::factory()->published()->create();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/vacancies/{$vacancy->id}/recusals", [
                'user_id' => $member->id, 'reason' => 'relative', 'notes' => 'Sibling of a candidate',
            ])->assertCreated();

        $this->actingAs($member, 'sanctum')->getJson("/api/qs-evaluations/{$vacancy->id}")->assertForbidden();
        $this->actingAs($member, 'sanctum')->getJson("/api/qs-evaluations/{$other->id}")->assertStatus(200);
    }

    public function test_recused_member_is_dropped_from_the_vacancy_board_and_not_an_active_member(): void
    {
        $member = User::factory()->hrmpsb()->create();
        $vacancy = Vacancy::factory()->published()->create();
        \App\Models\HrmpsbComposition::create([
            'user_id' => $member->id, 'hrmpsb_role' => 'chairperson', 'member_type' => 'principal', 'is_active' => true, 'assigned_by' => $member->id,
        ]);
        $service = app(\App\Services\HrmpsbCompositionService::class);

        $this->assertTrue($service->isActiveMember($member, $vacancy));
        $this->assertCount(1, $service->forVacancy($vacancy)->where('user_id', $member->id));

        app(\App\Services\ConflictOfInterestService::class)->recuse($vacancy, $member, 'relative');

        $this->assertFalse($service->isActiveMember($member, $vacancy));
        $this->assertCount(0, $service->forVacancy($vacancy)->where('user_id', $member->id));
        // Still a member for other vacancies.
        $this->assertTrue($service->isActiveMember($member, Vacancy::factory()->published()->create()));
    }

    public function test_board_member_who_applies_is_recused_automatically(): void
    {
        $member = User::factory()->hrmpsb()->create();
        $vacancy = Vacancy::factory()->published()->create();
        \App\Models\HrmpsbComposition::create([
            'user_id' => $member->id, 'hrmpsb_role' => 'secretariat', 'member_type' => 'principal', 'is_active' => true, 'assigned_by' => $member->id,
        ]);

        app(\App\Services\ConflictOfInterestService::class)->recuseApplicantIfStaff($vacancy, $member);

        $this->assertDatabaseHas('vacancy_recusals', [
            'vacancy_id' => $vacancy->id, 'user_id' => $member->id, 'reason' => 'applicant',
        ]);
    }

    public function test_plain_applicant_gets_no_recusal_row(): void
    {
        $applicant = User::factory()->applicant()->create();
        $vacancy = Vacancy::factory()->published()->create();

        app(\App\Services\ConflictOfInterestService::class)->recuseApplicantIfStaff($vacancy, $applicant);

        $this->assertDatabaseCount('vacancy_recusals', 0);
    }

    public function test_automatic_applicant_recusal_cannot_be_lifted_but_a_declared_one_can(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->hrmpsb()->create();
        $vacancy = Vacancy::factory()->published()->create();
        $service = app(\App\Services\ConflictOfInterestService::class);

        $auto = $service->recuse($vacancy, $member, 'applicant');
        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/admin/vacancies/{$vacancy->id}/recusals/{$auto->id}")->assertUnprocessable();

        $declared = $service->recuse($vacancy, User::factory()->hrmpsb()->create(), 'relative');
        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/admin/vacancies/{$vacancy->id}/recusals/{$declared->id}")->assertOk();
        $this->assertDatabaseMissing('vacancy_recusals', ['id' => $declared->id]);
    }

    public function test_a_board_member_can_apply_and_is_then_locked_out_of_that_vacancy(): void
    {
        $member = User::factory()->hrmpsb()->create();
        ApplicantProfile::factory()->complete()->withDocuments()->create(['user_id' => $member->id]);
        \App\Models\HrmpsbComposition::create([
            'user_id' => $member->id, 'hrmpsb_role' => 'secretariat', 'member_type' => 'principal',
            'is_active' => true, 'assigned_by' => $member->id,
        ]);
        $vacancy = Vacancy::factory()->published()->create();

        $this->actingAs($member, 'sanctum')->postJson('/api/applications', ['vacancy_id' => $vacancy->id])->assertCreated();

        $this->assertDatabaseHas('vacancy_recusals', ['vacancy_id' => $vacancy->id, 'user_id' => $member->id, 'reason' => 'applicant']);
        $this->actingAs($member, 'sanctum')->getJson("/api/qs-evaluations/{$vacancy->id}")->assertForbidden();
        $this->assertFalse(app(\App\Services\HrmpsbCompositionService::class)->isActiveMember($member, $vacancy));
    }

    public function test_an_imis_linked_applicant_is_marked_internal_and_others_are_not(): void
    {
        $employee = User::factory()->applicant()->create(['imis_user_id' => 9]);
        $outsider = User::factory()->applicant()->create();
        foreach ([$employee, $outsider] as $u) {
            ApplicantProfile::factory()->complete()->withDocuments()->create(['user_id' => $u->id]);
        }
        $vacancy = Vacancy::factory()->published()->create();

        $this->actingAs($employee, 'sanctum')->postJson('/api/applications', ['vacancy_id' => $vacancy->id])->assertCreated();
        $this->actingAs($outsider, 'sanctum')->postJson('/api/applications', ['vacancy_id' => $vacancy->id])->assertCreated();

        $this->assertSame(1, (int) Application::where('applicant_id', $employee->applicantProfile->id)->value('is_internal'));
        $this->assertSame(0, (int) Application::where('applicant_id', $outsider->applicantProfile->id)->value('is_internal'));
    }

    public function test_someone_elses_application_does_not_block_the_evaluator(): void
    {
        $member = User::factory()->admin()->create();
        $vacancy = Vacancy::factory()->published()->create();
        $this->applyAs(User::factory()->applicant()->create(), $vacancy);

        $this->actingAs($member, 'sanctum')->getJson("/api/qs-evaluations/{$vacancy->id}")->assertStatus(200);
    }
}
