<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\BackgroundInvestigationReport;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HoneypotProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function validRegisterPayload(array $overrides = []): array
    {
        return array_merge([
            'last_name' => 'Dela Cruz',
            'first_name' => 'Juan',
            'email' => 'juan.delacruz@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'privacy_policy_version' => '1.0',
        ], $overrides);
    }

    public function test_registration_succeeds_when_honeypot_field_is_empty(): void
    {
        $response = $this->postJson('/api/register', $this->validRegisterPayload(['website' => '']));

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'juan.delacruz@example.com']);
    }

    public function test_registration_is_rejected_when_honeypot_field_is_filled(): void
    {
        $response = $this->postJson('/api/register', $this->validRegisterPayload(['website' => 'http://spam.example']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('website');
        $this->assertDatabaseMissing('users', ['email' => 'juan.delacruz@example.com']);
    }

    public function test_background_investigation_upload_is_rejected_when_honeypot_field_is_filled(): void
    {
        $vacancy = Vacancy::factory()->create();
        $profile = ApplicantProfile::factory()->create();
        $application = Application::create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $profile->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $report = BackgroundInvestigationReport::create([
            'application_id' => $application->id,
            'investigator_name' => 'Detective John',
            'investigator_email' => 'john@example.com',
            'token' => 'test-token-123',
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post("/background-investigation/upload/{$report->token}", [
                'on_competencies' => str_repeat('a', 1000),
                'on_performance' => str_repeat('b', 1000),
                'file' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
                'website' => 'filled-by-a-bot',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('website');
        $this->assertNull($report->fresh()->submitted_at);
    }
}
