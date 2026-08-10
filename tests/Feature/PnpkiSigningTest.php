<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CsForm;
use App\Services\PnpkiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Tests\TestCase;

/**
 * PNPKI signing is not implemented — CSC has not supplied the SDK or
 * certificates. These tests pin the fail-closed behaviour so nobody can
 * accidentally re-enable a stub that reports forms as signed without signing
 * them.
 */
class PnpkiSigningTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reports_itself_unconfigured_without_an_api_key(): void
    {
        Config::set('services.pnpki.api_key', null);

        $this->assertFalse((new PnpkiService)->isConfigured());
    }

    public function test_signing_refuses_instead_of_silently_marking_the_form_signed(): void
    {
        // Even with a key present, there is no real implementation behind it.
        Config::set('services.pnpki.api_key', 'anything');

        $form = new CsForm;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/not implemented/i');

        (new PnpkiService)->sign($form, 'Juan Dela Cruz');
    }

    public function test_a_form_is_never_reported_as_verified(): void
    {
        $form = new CsForm(['signed_at' => now()]);

        $this->assertFalse(
            (new PnpkiService)->verify($form),
            'a signed_at timestamp is not cryptographic proof and must not verify'
        );
    }
}
