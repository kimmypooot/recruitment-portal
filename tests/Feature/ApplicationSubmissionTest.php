<?php

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_can_submit_application_to_published_vacancy(): void
    {
        $applicant = User::factory()->applicant()->create();
        $profile   = ApplicantProfile::factory()->complete()->create(['user_id' => $applicant->id]);
        $vacancy   = Vacancy::factory()->published()->create();

        $response = $this->actingAs($applicant, 'sanctum')
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id]);

        $response->assertCreated();
        $this->assertDatabaseHas('applications', [
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
        ]);
    }

    public function test_hr_officer_cannot_submit_application(): void
    {
        $hrOfficer = User::factory()->hrOfficer()->create();
        $vacancy   = Vacancy::factory()->published()->create();

        $this->actingAs($hrOfficer, 'sanctum')
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id])
            ->assertForbidden();
    }

    public function test_applicant_cannot_apply_with_incomplete_profile(): void
    {
        $applicant = User::factory()->applicant()->create();
        // No profile created — isComplete() will fail
        $vacancy = Vacancy::factory()->published()->create();

        $this->actingAs($applicant, 'sanctum')
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id])
            ->assertUnprocessable();
    }

    public function test_applicant_cannot_apply_to_same_vacancy_twice(): void
    {
        $applicant = User::factory()->applicant()->create();
        $profile   = ApplicantProfile::factory()->complete()->create(['user_id' => $applicant->id]);
        $vacancy   = Vacancy::factory()->published()->create();

        $this->actingAs($applicant, 'sanctum')
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id])
            ->assertCreated();

        $this->actingAs($applicant, 'sanctum')
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id])
            ->assertUnprocessable();
    }
}
