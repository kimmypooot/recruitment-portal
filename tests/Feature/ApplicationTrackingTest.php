<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function submitApplication(): array
    {
        $applicant = User::factory()->applicant()->create(['email' => 'juan.delacruz@example.com']);
        $profile = ApplicantProfile::factory()->complete()->withDocuments()->create(['user_id' => $applicant->id]);
        $vacancy = Vacancy::factory()->published()->create();

        $this->actingAs($applicant, 'sanctum')
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id])
            ->assertCreated();

        $application = Application::where('applicant_id', $profile->id)->firstOrFail();

        return compact('applicant', 'profile', 'vacancy', 'application');
    }

    public function test_submitting_an_application_generates_a_reference_code(): void
    {
        ['application' => $application] = $this->submitApplication();

        $this->assertNotNull($application->reference_code);
        $this->assertStringStartsWith('CSC-', $application->reference_code);
    }

    public function test_correct_reference_code_and_email_returns_status(): void
    {
        ['applicant' => $applicant, 'application' => $application] = $this->submitApplication();

        $response = $this->postJson('/api/track', [
            'reference_code' => $application->reference_code,
            'email' => $applicant->email,
        ]);

        $response->assertOk();
        $response->assertJson([
            'reference_code' => $application->reference_code,
            'status' => 'submitted',
        ]);
        $response->assertJsonStructure(['reference_code', 'status', 'submitted_at', 'last_updated_at', 'vacancy' => ['position_title', 'place_of_assignment']]);
    }

    public function test_lookup_is_case_insensitive_on_code_and_email(): void
    {
        ['applicant' => $applicant, 'application' => $application] = $this->submitApplication();

        $this->postJson('/api/track', [
            'reference_code' => strtolower($application->reference_code),
            'email' => strtoupper($applicant->email),
        ])->assertOk();
    }

    public function test_wrong_email_for_a_real_code_is_rejected(): void
    {
        ['application' => $application] = $this->submitApplication();

        $response = $this->postJson('/api/track', [
            'reference_code' => $application->reference_code,
            'email' => 'someone-else@example.com',
        ]);

        $response->assertNotFound();
    }

    public function test_nonexistent_code_gives_the_same_generic_message_as_a_wrong_email(): void
    {
        ['applicant' => $applicant] = $this->submitApplication();

        $wrongEmail = $this->postJson('/api/track', [
            'reference_code' => 'CSC-DOESNOTEX',
            'email' => 'nobody@example.com',
        ]);
        $wrongCode = $this->postJson('/api/track', [
            'reference_code' => 'CSC-DOESNOTEX',
            'email' => $applicant->email,
        ]);

        $wrongEmail->assertNotFound();
        $wrongCode->assertNotFound();
        $this->assertSame($wrongEmail->json('message'), $wrongCode->json('message'));
    }

    public function test_track_endpoint_does_not_leak_documents_or_other_applications(): void
    {
        ['applicant' => $applicant, 'application' => $application] = $this->submitApplication();

        $response = $this->postJson('/api/track', [
            'reference_code' => $application->reference_code,
            'email' => $applicant->email,
        ]);

        $response->assertJsonMissingPath('documents');
        $response->assertJsonMissingPath('applicant');
        $response->assertJsonMissingPath('id');
    }

    public function test_track_endpoint_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/track', [
                'reference_code' => 'CSC-NOPE0000',
                'email' => 'rate-limit-probe@example.com',
            ])->assertNotFound();
        }

        $this->postJson('/api/track', [
            'reference_code' => 'CSC-NOPE0000',
            'email' => 'rate-limit-probe@example.com',
        ])->assertStatus(429);
    }
}
