<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\HrmpsbComposition;
use App\Models\PdsDeclaration;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\AnonymizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PdsEndpointTest extends TestCase
{
    use RefreshDatabase;

    /** A profile complete enough for PdsCompletenessChecker to allow generation. */
    private function readyProfile(?User $user = null): ApplicantProfile
    {
        $user ??= User::factory()->applicant()->create();

        $profile = ApplicantProfile::factory()->complete()->pdsPersonalInfo()->create(['user_id' => $user->id]);

        $profile->familyBackground()->create([
            'father_surname'           => 'Dela Cruz',
            'father_first_name'        => 'Pedro',
            'mother_maiden_surname'    => 'Reyes',
            'mother_maiden_first_name' => 'Maria',
        ]);
        $profile->educationalAttainments()->create(['level' => 'elementary', 'school_name' => 'Tacloban Elem.']);
        $profile->educationalAttainments()->create(['level' => 'secondary', 'school_name' => 'Tacloban High']);
        $profile->declaration()->create(array_fill_keys(PdsDeclaration::REQUIRED_ANSWERS, false));
        foreach (range(1, 3) as $i) {
            $profile->references()->create(['name' => "Reference {$i}", 'sort_order' => $i]);
        }

        return $profile->fresh();
    }

    private function boardMember(): User
    {
        $user = User::factory()->hrmpsb()->create();

        HrmpsbComposition::create([
            'user_id'     => $user->id,
            'hrmpsb_role' => 'director-representative',
            'is_active'   => true,
            'assigned_by' => User::factory()->admin()->create()->id,
        ]);

        return $user;
    }

    private function applicationFor(ApplicantProfile $profile): Application
    {
        return Application::create([
            'vacancy_id'   => Vacancy::factory()->published()->create()->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
        ]);
    }

    // --- Completeness -------------------------------------------------------

    public function test_completeness_requires_authentication(): void
    {
        $this->getJson('/api/profile/pds/completeness')->assertUnauthorized();
    }

    public function test_completeness_reports_blocking_sections(): void
    {
        $user = User::factory()->applicant()->create();
        ApplicantProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/profile/pds/completeness');

        $response->assertOk()
            ->assertJsonPath('can_generate', false)
            ->assertJsonStructure(['sections', 'blocking', 'can_generate']);
    }

    // --- Own download -------------------------------------------------------

    public function test_an_applicant_can_download_their_own_pds_as_xlsx(): void
    {
        $user = User::factory()->applicant()->create();
        $this->readyProfile($user);

        $response = $this->actingAs($user, 'sanctum')->get('/api/profile/pds/download?format=xlsx');

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition') ?? '');
    }

    public function test_an_incomplete_profile_is_refused_with_the_blocking_sections(): void
    {
        $user = User::factory()->applicant()->create();
        ApplicantProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile/pds/download')
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'blocking']);
    }

    public function test_an_unsupported_format_is_rejected(): void
    {
        $user = User::factory()->applicant()->create();
        $this->readyProfile($user);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile/pds/download?format=docx')
            ->assertStatus(422);
    }

    public function test_a_missing_libreoffice_returns_service_unavailable_rather_than_a_500(): void
    {
        Config::set('pds.soffice_path', '/definitely/not/a/real/soffice');

        $user = User::factory()->applicant()->create();
        $this->readyProfile($user);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile/pds/download?format=pdf')
            ->assertStatus(503)
            ->assertJsonPath('message', fn ($m) => str_contains((string) $m, 'LibreOffice'));
    }

    public function test_generating_stamps_pds_generated_at(): void
    {
        $user    = User::factory()->applicant()->create();
        $profile = $this->readyProfile($user);

        $this->assertNull($profile->pds_generated_at);

        $this->actingAs($user, 'sanctum')->get('/api/profile/pds/download');

        $this->assertNotNull($profile->fresh()->pds_generated_at);
    }

    // --- Board access -------------------------------------------------------

    public function test_a_board_member_can_download_a_candidates_pds(): void
    {
        $application = $this->applicationFor($this->readyProfile());

        $this->actingAs($this->boardMember(), 'sanctum')
            ->get("/api/hrmpsb/applications/{$application->id}/pds")
            ->assertOk();
    }

    public function test_an_admin_can_download_a_candidates_pds(): void
    {
        $application = $this->applicationFor($this->readyProfile());
        $admin       = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->get("/api/hrmpsb/applications/{$application->id}/pds")
            ->assertOk();
    }

    public function test_another_applicant_cannot_download_someone_elses_pds(): void
    {
        $application = $this->applicationFor($this->readyProfile());
        $outsider    = User::factory()->applicant()->create();

        // The route itself is role-gated before the policy is reached.
        $this->actingAs($outsider, 'sanctum')
            ->getJson("/api/hrmpsb/applications/{$application->id}/pds")
            ->assertForbidden();
    }

    public function test_a_masked_candidate_is_withheld_from_a_board_member_during_blind_deliberation(): void
    {
        $application = $this->applicationFor($this->readyProfile());
        app(AnonymizationService::class)->generateForApplication($application);

        $this->actingAs($this->boardMember(), 'sanctum')
            ->getJson("/api/hrmpsb/applications/{$application->id}/pds")
            ->assertForbidden();
    }

    public function test_an_unmasked_candidate_is_available_again(): void
    {
        $profile     = $this->readyProfile();
        $application = $this->applicationFor($profile);
        $admin       = User::factory()->admin()->create();

        app(AnonymizationService::class)->generateForApplication($application);
        app(AnonymizationService::class)->unmaskVacancy($application->vacancy, $admin);

        $this->actingAs($this->boardMember(), 'sanctum')
            ->get("/api/hrmpsb/applications/{$application->id}/pds")
            ->assertOk();
    }

    public function test_a_user_who_may_unmask_identities_is_not_blocked_by_masking(): void
    {
        $application = $this->applicationFor($this->readyProfile());
        app(AnonymizationService::class)->generateForApplication($application);

        // Admins hold the unmask-identities gate.
        $this->actingAs(User::factory()->admin()->create(), 'sanctum')
            ->get("/api/hrmpsb/applications/{$application->id}/pds")
            ->assertOk();
    }

    public function test_masking_can_be_switched_off_for_unconditional_board_access(): void
    {
        Config::set('pds.board_access_respects_anonymization', false);

        $application = $this->applicationFor($this->readyProfile());
        app(AnonymizationService::class)->generateForApplication($application);

        $this->actingAs($this->boardMember(), 'sanctum')
            ->get("/api/hrmpsb/applications/{$application->id}/pds")
            ->assertOk();
    }
}
