<?php

namespace App\Services;

use App\Models\CsForm;

/**
 * PNPKI (Philippine National Public Key Infrastructure) digital signature stub.
 *
 * Replace the bodies of sign() and verify() once CSC provides:
 *   - PNPKI SDK / API endpoint
 *   - Certificate credentials (PNPKI_CERT_PATH, PNPKI_CERT_PASSWORD in .env)
 *   - API key (PNPKI_API_KEY in .env)
 *
 * Until then, sign() marks the form as "signed" with a placeholder timestamp
 * and verify() always returns false (unsigned).
 */
class PnpkiService
{
    public function sign(CsForm $form, string $signerName): bool
    {
        // TODO: integrate PNPKI SDK here
        // Example SDK call (replace with real implementation):
        // $sdk = new PnpkiSdk(config('services.pnpki.endpoint'), config('services.pnpki.api_key'));
        // $result = $sdk->sign($form->file_path, $signerName);
        // if (!$result->success) return false;

        $form->update(['signed_at' => now()]);
        AuditLog::record('cs_form_signed', $form->application);

        return true;
    }

    public function verify(CsForm $form): bool
    {
        // TODO: verify signature via PNPKI SDK
        return $form->isSigned();
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.pnpki.api_key'));
    }
}
