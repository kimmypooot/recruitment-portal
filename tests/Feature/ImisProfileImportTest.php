<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImisProfileImportTest extends TestCase
{
    use RefreshDatabase;

    private const BYTES = '%PDF-1.4 fake';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['services.imis.api_url' => 'https://imis.test', 'services.imis.api_token' => 'tok']);
    }

    private function employee(array $profile = []): User
    {
        $user = User::factory()->applicant()->create(['imis_user_id' => 77]);
        ApplicantProfile::factory()->create(['user_id' => $user->id] + $profile);

        return $user;
    }

    /** What IMIS answers, overridable per test. */
    private function imis(array $pds = [], ?array $documents = null, ?string $fileBytes = null, int $pdsStatus = 200): void
    {
        $pds = $pds + [
            'source' => 'verified_pds',
            'identity' => ['sex' => 'female', 'birthday' => '1990-04-05', 'employee_number' => 'CSC-001'],
            'personal' => [
                'civil_status' => 'married', 'place_of_birth' => 'Tacloban', 'mobile_no' => '09171112222', 'tin_no' => '123-456',
                'residential_city' => 'Tacloban City', 'residential_province' => 'Leyte', 'residential_street' => 'Rizal St',
            ],
            'education' => [['level' => 'college', 'school' => 'UP Tacloban', 'degree' => 'BS Stat', 'attendance_from' => 2008, 'attendance_to' => 2012, 'year_graduated' => 2012]],
            'eligibility' => [['eligibility' => 'Career Service Professional']],
            'work_experience' => [['position_title' => 'Admin Aide', 'agency' => 'CSC RO VIII', 'date_from' => '2013-01-02', 'date_to' => null, 'is_government' => true, 'monthly_salary' => 15000, 'salary_grade' => '4']],
            'learning_development' => [['title' => 'Records Management', 'date_from' => '2024-02-01', 'date_to' => '2024-02-02', 'hours' => 16, 'ld_type' => 'technical', 'sponsor' => 'CSC']],
        ];
        $documents ??= [['id' => 5, 'document_type' => 'personal_data_sheet', 'mime_type' => 'application/pdf', 'sha256' => hash('sha256', self::BYTES)]];

        Http::fake([
            'imis.test/api/v1/recruitment/employees/77/pds' => Http::response(['data' => $pds], $pdsStatus),
            'imis.test/api/v1/recruitment/employees/77/documents' => Http::response(['data' => $documents]),
            'imis.test/api/v1/recruitment/employees/77/documents/*/file' => Http::response($fileBytes ?? self::BYTES),
        ]);
    }

    public function test_it_fills_the_profile_lists_and_documents_from_imis(): void
    {
        $user = $this->employee(['gender' => null, 'civil_status' => null, 'birthday' => null, 'mobile_number' => null, 'eligibility' => null]);
        $this->imis();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/profile/import-imis')->assertOk();

        $profile = $user->applicantProfile->fresh();
        $this->assertSame('Female', $profile->gender);
        $this->assertSame('Married', $profile->civil_status);
        $this->assertSame('1990-04-05', $profile->birthday->toDateString());
        $this->assertSame('09171112222', $profile->mobile_number);
        $this->assertSame('Career Service Professional Eligibility', $profile->eligibility);
        $this->assertSame('Tacloban City', $profile->city_municipality);
        $this->assertNotNull($profile->imis_imported_at);

        $this->assertDatabaseHas('educational_attainments', ['applicant_profile_id' => $profile->id, 'level' => 'College', 'school_name' => 'UP Tacloban']);
        $this->assertDatabaseHas('work_experiences', ['applicant_profile_id' => $profile->id, 'position_title' => 'Admin Aide', 'is_present' => 1]);
        $this->assertDatabaseHas('trainings', ['applicant_profile_id' => $profile->id, 'title' => 'Records Management', 'ld_type' => 'Technical']);

        Storage::disk('local')->assertExists($profile->pds_path);
        $this->assertSame(self::BYTES, Storage::disk('local')->get($profile->pds_path));
        $this->assertSame(['PDS'], $response->json('summary.documents'));
        $this->assertTrue($response->json('summary.verified'));
    }

    public function test_running_it_again_adds_nothing_and_keeps_what_the_applicant_entered(): void
    {
        $user = $this->employee(['civil_status' => 'Widowed', 'gender' => null]);
        $this->imis();

        $this->actingAs($user, 'sanctum')->postJson('/api/profile/import-imis')->assertOk();
        $second = $this->actingAs($user, 'sanctum')->postJson('/api/profile/import-imis')->assertOk();

        $profile = $user->applicantProfile->fresh();
        $this->assertSame('Widowed', $profile->civil_status);
        $this->assertSame('Female', $profile->gender);
        $this->assertSame(1, $profile->educationalAttainments()->count());
        $this->assertSame(1, $profile->workExperiences()->count());
        $this->assertSame(1, $profile->trainings()->count());
        $this->assertSame(0, $second->json('summary.education') + $second->json('summary.work_experience') + $second->json('summary.trainings'));
        $this->assertSame([], $second->json('summary.documents'));
    }

    public function test_an_existing_document_is_not_replaced(): void
    {
        $user = $this->employee(['pds_path' => 'profile-documents/mine.pdf']);
        $this->imis();

        $this->actingAs($user, 'sanctum')->postJson('/api/profile/import-imis')->assertOk();

        $this->assertSame('profile-documents/mine.pdf', $user->applicantProfile->fresh()->pds_path);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/file'));
    }

    public function test_a_document_that_is_not_a_pdf_is_left_out_with_a_note(): void
    {
        $user = $this->employee(['pds_path' => null]);
        $this->imis(documents: [['id' => 5, 'document_type' => 'personal_data_sheet', 'mime_type' => 'image/png', 'sha256' => 'x']]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/profile/import-imis')->assertOk();

        $this->assertNull($user->applicantProfile->fresh()->pds_path);
        $this->assertStringContainsString('not a PDF', $response->json('summary.notes.0'));
    }

    public function test_a_document_that_fails_its_hash_is_left_out_with_a_note(): void
    {
        $user = $this->employee(['pds_path' => null]);
        $this->imis(fileBytes: 'tampered');

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/profile/import-imis')->assertOk();

        $this->assertNull($user->applicantProfile->fresh()->pds_path);
        $this->assertStringContainsString('integrity', $response->json('summary.notes.0'));
    }

    public function test_without_a_verified_pds_it_says_so(): void
    {
        $user = $this->employee();
        $this->imis(['source' => null, 'personal' => null, 'education' => [], 'eligibility' => [], 'work_experience' => []]);

        $this->actingAs($user, 'sanctum')->postJson('/api/profile/import-imis')
            ->assertOk()
            ->assertJsonPath('summary.verified', false);
    }

    public function test_someone_not_linked_to_imis_cannot_import(): void
    {
        $user = User::factory()->applicant()->create();
        ApplicantProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')->postJson('/api/profile/import-imis')->assertUnprocessable();
    }

    public function test_an_imis_failure_is_reported_not_swallowed(): void
    {
        $user = $this->employee();
        $this->imis(pdsStatus: 404);

        $this->actingAs($user, 'sanctum')->postJson('/api/profile/import-imis')
            ->assertStatus(502)
            ->assertJsonPath('message', 'IMIS has no current employee record for you.');
    }

    public function test_it_is_off_while_the_imis_link_is_not_configured(): void
    {
        config(['services.imis.api_token' => null]);

        $this->actingAs($this->employee(), 'sanctum')->postJson('/api/profile/import-imis')->assertUnprocessable();
    }
}
