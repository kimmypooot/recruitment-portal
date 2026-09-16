<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AnonymizationToken;
use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\HrmpsbComposition;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * HrmpsbController::applicantProfile() already withheld `name` while an
 * application's identity is masked, but handed out document_links (and
 * serveDocument() streamed the actual file) unconditionally — the PDS
 * carries the same name, plus a photo and address, so it defeated the
 * masking it sat right next to. Masking is live from QS-lock onward
 * (QsEvaluationController::lock() generates the tokens), not just at final
 * deliberation.
 */
class HrmpsbDocumentMaskingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        Storage::fake('local');

        $this->admin = User::factory()->admin()->create();
        $vacancy = Vacancy::factory()->published()->create();
        $profile = ApplicantProfile::factory()->complete()->create([
            'pds_path' => 'profile-documents/1/pds.pdf',
        ]);
        Storage::disk('local')->put('profile-documents/1/pds.pdf', '%PDF-1.4 fake');

        $this->application = Application::create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => $profile->id,
            'status' => 'qualified',
            'submitted_at' => now(),
        ]);
    }

    private function boardMember(): User
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

    public function test_document_links_are_withheld_while_masked(): void
    {
        AnonymizationToken::create(['application_id' => $this->application->id, 'token' => 'AO2-0101-001']);
        $member = $this->boardMember();

        $response = $this->actingAs($member, 'sanctum')
            ->getJson("/api/hrmpsb/applications/{$this->application->id}/profile");

        $response->assertOk();
        $response->assertJson([
            'unmasked' => false,
            'name' => null,
            'document_links' => ['pds' => null, 'tor' => null, 'app_letter' => null, 'ipcr' => null, 'coe' => null],
        ]);
        // Existence flags are not identifying on their own — still visible.
        $response->assertJsonPath('documents.pds', true);
    }

    public function test_document_links_are_restored_once_unmasked(): void
    {
        AnonymizationToken::create([
            'application_id' => $this->application->id,
            'token' => 'AO2-0101-001',
            'unmasked_at' => now(),
            'unmasked_by' => $this->admin->id,
        ]);
        $member = $this->boardMember();

        $response = $this->actingAs($member, 'sanctum')
            ->getJson("/api/hrmpsb/applications/{$this->application->id}/profile");

        $response->assertOk();
        $response->assertJson(['unmasked' => true]);
        $this->assertNotNull($response->json('document_links.pds'));
    }

    public function test_admin_always_sees_document_links_regardless_of_mask_state(): void
    {
        AnonymizationToken::create(['application_id' => $this->application->id, 'token' => 'AO2-0101-001']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/hrmpsb/applications/{$this->application->id}/profile");

        $response->assertOk();
        $this->assertNotNull($response->json('document_links.pds'));
    }

    public function test_serve_document_rejects_a_board_member_while_masked_even_with_a_direct_url(): void
    {
        AnonymizationToken::create(['application_id' => $this->application->id, 'token' => 'AO2-0101-001']);
        $member = $this->boardMember();

        $this->actingAs($member, 'sanctum')
            ->get("/api/hrmpsb/applications/{$this->application->id}/documents/pds")
            ->assertForbidden();
    }

    public function test_serve_document_works_for_a_board_member_once_unmasked(): void
    {
        AnonymizationToken::create([
            'application_id' => $this->application->id,
            'token' => 'AO2-0101-001',
            'unmasked_at' => now(),
            'unmasked_by' => $this->admin->id,
        ]);
        $member = $this->boardMember();

        $this->actingAs($member, 'sanctum')
            ->get("/api/hrmpsb/applications/{$this->application->id}/documents/pds")
            ->assertOk();
    }

    public function test_serve_document_still_works_for_admin_regardless_of_mask_state(): void
    {
        AnonymizationToken::create(['application_id' => $this->application->id, 'token' => 'AO2-0101-001']);

        $this->actingAs($this->admin, 'sanctum')
            ->get("/api/hrmpsb/applications/{$this->application->id}/documents/pds")
            ->assertOk();
    }

    // ── BeiRatingController::index() has its own, separately-built
    // document_links payload for the same reason — same bug, same fix. ──

    public function test_bei_ratings_index_withholds_document_links_while_masked(): void
    {
        AnonymizationToken::create(['application_id' => $this->application->id, 'token' => 'AO2-0101-001']);
        $chair = $this->boardMember();

        $response = $this->actingAs($chair, 'sanctum')
            ->getJson("/api/bei-ratings/{$this->application->vacancy_id}");

        $response->assertOk();
        $app = collect($response->json('applications'))->firstWhere('id', $this->application->id);
        $this->assertNotNull($app);
        $this->assertFalse($app['unmasked']);
        $this->assertNull($app['name']);
        $this->assertNull($app['document_links']['pds']);
        // Existence flag is unaffected — still visible.
        $this->assertTrue($app['documents']['has_pds']);
    }

    public function test_bei_ratings_index_restores_document_links_once_unmasked(): void
    {
        AnonymizationToken::create([
            'application_id' => $this->application->id,
            'token' => 'AO2-0101-001',
            'unmasked_at' => now(),
            'unmasked_by' => $this->admin->id,
        ]);
        $chair = $this->boardMember();

        $response = $this->actingAs($chair, 'sanctum')
            ->getJson("/api/bei-ratings/{$this->application->vacancy_id}");

        $response->assertOk();
        $app = collect($response->json('applications'))->firstWhere('id', $this->application->id);
        $this->assertNotNull($app['document_links']['pds']);
    }
}
