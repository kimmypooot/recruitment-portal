<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicantProfile;
use App\Models\CsForm;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\FormGeneratorService;
use App\Services\PnpkiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsFormGenerationTest extends TestCase
{
    use RefreshDatabase;

    private User $hrManager;
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->hrManager = User::factory()->hrManager()->create();

        $vacancy = Vacancy::factory()->published()->create([
            'position_title'      => 'Administrative Officer II',
            'salary_grade'        => 11,
            'place_of_assignment' => 'CSC RO VIII',
        ]);

        $profile = ApplicantProfile::factory()->complete()->create();
        $profile->user->update([
            'first_name'  => 'Juan',
            'last_name'   => 'dela Cruz',
            'middle_name' => 'Santos',
        ]);

        $this->application = Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    // ── FormGeneratorService unit tests ───────────────────────────────────

    public function test_generate_creates_pdf_file_on_disk(): void
    {
        $service = new FormGeneratorService();
        $form = $service->generate($this->application, '33A', $this->hrManager);

        Storage::disk('local')->assertExists($form->file_path);
    }

    public function test_generated_pdf_is_non_empty(): void
    {
        $service = new FormGeneratorService();
        $form = $service->generate($this->application, '33A', $this->hrManager);

        $content = Storage::disk('local')->get($form->file_path);
        $this->assertStringStartsWith('%PDF', $content, 'File should be a valid PDF (starts with %PDF header)');
        $this->assertGreaterThan(1024, strlen($content), 'PDF should be larger than 1 KB');
    }

    public function test_generate_persists_cs_form_record(): void
    {
        $service = new FormGeneratorService();
        $service->generate($this->application, '33A', $this->hrManager);

        $this->assertDatabaseHas('cs_forms', [
            'application_id' => $this->application->id,
            'form_type'      => '33A',
            'generated_by'   => $this->hrManager->id,
        ]);
    }

    public function test_generate_all_three_form_types(): void
    {
        $service = new FormGeneratorService();

        foreach (['33A', '33B', 'form1'] as $type) {
            $form = $service->generate($this->application, $type, $this->hrManager);
            Storage::disk('local')->assertExists($form->file_path);

            $content = Storage::disk('local')->get($form->file_path);
            $this->assertStringStartsWith('%PDF', $content, "Form {$type} should produce a valid PDF");
        }
    }

    public function test_generate_throws_for_unknown_form_type(): void
    {
        $service = new FormGeneratorService();

        $this->expectException(\InvalidArgumentException::class);
        $service->generate($this->application, 'bogus', $this->hrManager);
    }

    public function test_regenerating_same_type_updates_existing_record(): void
    {
        $service = new FormGeneratorService();
        $service->generate($this->application, '33A', $this->hrManager);
        $service->generate($this->application, '33A', $this->hrManager);

        $this->assertSame(1, CsForm::where([
            'application_id' => $this->application->id,
            'form_type'      => '33A',
        ])->count(), 'Regenerating should upsert, not duplicate');
    }

    // ── HTTP endpoint tests ────────────────────────────────────────────────

    public function test_generate_endpoint_returns_201(): void
    {
        $this->actingAs($this->hrManager, 'sanctum')
            ->postJson("/api/applications/{$this->application->id}/forms", [
                'form_type' => '33A',
            ])
            ->assertCreated()
            ->assertJsonFragment(['form_type' => '33A']);
    }

    public function test_generate_endpoint_rejects_invalid_form_type(): void
    {
        $this->actingAs($this->hrManager, 'sanctum')
            ->postJson("/api/applications/{$this->application->id}/forms", [
                'form_type' => 'invalid',
            ])
            ->assertUnprocessable();
    }

    public function test_index_endpoint_lists_forms_for_application(): void
    {
        $service = new FormGeneratorService();
        $service->generate($this->application, '33A', $this->hrManager);
        $service->generate($this->application, '33B', $this->hrManager);

        $response = $this->actingAs($this->hrManager, 'sanctum')
            ->getJson("/api/applications/{$this->application->id}/forms")
            ->assertOk();

        $this->assertCount(2, $response->json('forms'));
        $this->assertArrayHasKey('pnpki_ready', $response->json());
    }

    public function test_download_endpoint_streams_pdf(): void
    {
        $service = new FormGeneratorService();
        $form = $service->generate($this->application, 'form1', $this->hrManager);

        $this->actingAs($this->hrManager, 'sanctum')
            ->get("/api/forms/{$form->id}/download")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_download_returns_404_when_file_missing(): void
    {
        $form = CsForm::create([
            'application_id' => $this->application->id,
            'form_type'      => '33A',
            'file_path'      => 'cs-forms/nonexistent/file.pdf',
            'generated_by'   => $this->hrManager->id,
            'generated_at'   => now(),
        ]);

        $this->actingAs($this->hrManager, 'sanctum')
            ->get("/api/forms/{$form->id}/download")
            ->assertNotFound();
    }

    public function test_mark_submitted_endpoint_returns_ok(): void
    {
        $service = new FormGeneratorService();
        $form = $service->generate($this->application, '33A', $this->hrManager);

        $this->actingAs($this->hrManager, 'sanctum')
            ->patchJson("/api/forms/{$form->id}/mark-submitted")
            ->assertOk()
            ->assertJsonFragment(['message' => 'Form marked as submitted to CSC.']);
    }

    // ── PnpkiService unit tests ───────────────────────────────────────────

    public function test_pnpki_is_not_configured_without_api_key(): void
    {
        config(['services.pnpki.api_key' => null]);
        $pnpki = new PnpkiService();

        $this->assertFalse($pnpki->isConfigured());
    }

    public function test_pnpki_is_configured_when_api_key_present(): void
    {
        config(['services.pnpki.api_key' => 'test-key-123']);
        $pnpki = new PnpkiService();

        $this->assertTrue($pnpki->isConfigured());
    }

    /**
     * Previously asserted that sign() stamped signed_at and returned true. It
     * did that without performing any cryptography, so a form could be reported
     * as digitally signed when nothing had been signed. Signing now fails closed
     * until CSC supplies the PNPKI SDK and certificates.
     */
    public function test_pnpki_sign_refuses_until_it_is_really_implemented(): void
    {
        $service = new FormGeneratorService();
        $form = $service->generate($this->application, '33A', $this->hrManager);

        $this->assertNull($form->signed_at);

        try {
            (new PnpkiService())->sign($form, 'Test Signer');
            $this->fail('signing should refuse while PNPKI is unimplemented');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('not implemented', $e->getMessage());
        }

        $this->assertNull($form->fresh()->signed_at, 'the form must not be marked signed');
    }

    public function test_pnpki_verify_returns_false_for_unsigned_form(): void
    {
        $form = CsForm::create([
            'application_id' => $this->application->id,
            'form_type'      => '33A',
            'file_path'      => 'cs-forms/test.pdf',
            'generated_by'   => $this->hrManager->id,
            'generated_at'   => now(),
            'signed_at'      => null,
        ]);

        $pnpki = new PnpkiService();
        $this->assertFalse($pnpki->verify($form));
    }

    /**
     * A signed_at timestamp is not cryptographic proof of anything, so verify()
     * must not treat it as one. It stays false until real verification exists.
     */
    public function test_pnpki_verify_does_not_treat_a_timestamp_as_proof(): void
    {
        $form = CsForm::create([
            'application_id' => $this->application->id,
            'form_type'      => '33A',
            'file_path'      => 'cs-forms/test.pdf',
            'generated_by'   => $this->hrManager->id,
            'generated_at'   => now(),
            'signed_at'      => now(),
        ]);

        $pnpki = new PnpkiService();
        $this->assertFalse($pnpki->verify($form));
    }

    public function test_sign_endpoint_returns_422_when_pnpki_not_configured(): void
    {
        config(['services.pnpki.api_key' => null]);

        $service = new FormGeneratorService();
        $form = $service->generate($this->application, '33A', $this->hrManager);

        $this->actingAs($this->hrManager, 'sanctum')
            ->patchJson("/api/forms/{$form->id}/sign")
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'PNPKI is not yet configured. Contact CSC for credentials.']);
    }

    /**
     * Configuring an API key is not enough — there is no implementation behind
     * it. The endpoint reports 501 Not Implemented rather than claiming a
     * success it cannot deliver.
     */
    public function test_sign_endpoint_reports_not_implemented_even_when_configured(): void
    {
        config(['services.pnpki.api_key' => 'test-key-123']);

        $service = new FormGeneratorService();
        $form = $service->generate($this->application, '33A', $this->hrManager);

        $this->actingAs($this->hrManager, 'sanctum')
            ->patchJson("/api/forms/{$form->id}/sign")
            ->assertStatus(501);

        $this->assertFalse($form->fresh()->isSigned());
    }
}
