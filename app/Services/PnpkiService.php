<?php

declare(strict_types=1);

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
    /**
     * Refuses rather than pretending.
     *
     * This previously stamped `signed_at` and returned true without performing
     * any cryptography, so setting PNPKI_API_KEY to any value would have made
     * the system report CSC forms as digitally signed when nothing had been
     * signed. On a document submitted to CSC that is a false attestation, so
     * the unimplemented path now fails closed.
     *
     * Replace the body once CSC provides the PNPKI SDK/endpoint and
     * certificate credentials; `isConfigured()` already gates the caller.
     */
    public function sign(CsForm $form, string $signerName): bool
    {
        if ($form->application !== null) {
            AuditLog::record('cs_form_sign_attempted_without_pnpki', $form->application);
        }

        throw new \RuntimeException(
            'PNPKI digital signing is not implemented. CSC has not supplied the SDK '
            .'or certificate credentials, and marking a form as signed without a real '
            .'signature would misrepresent it. Print and wet-sign the form instead — '
            .'CS Form No. 212 (Revised 2026) accepts a wet signature, e-signature, or '
            .'digital certificate.'
        );
    }

    /**
     * Whether the form carries a verified PNPKI signature.
     *
     * Always false until real verification exists: `signed_at` alone proves
     * nothing cryptographically, and treating it as proof would let an
     * unverified form pass as verified.
     */
    public function verify(CsForm $form): bool
    {
        return false;
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.pnpki.api_key'));
    }
}
