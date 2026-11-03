<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Signs HR staff and board members in from the IMIS portal. IMIS sends the
 * browser to /sso with a one-time token; the page posts it here, and this
 * redeems it server-to-server at IMIS, which says who it was issued for and
 * what role they hold in this system (`role_code`: `admin` or `hrmpsb`).
 *
 * IMIS is where these people are managed. The account here is linked by
 * `imis_user_id` (else once by email), created on first sign-in, and has its
 * name, email and role rewritten from IMIS on every sign-in. It has no password
 * to use. External applicants are not in IMIS and never sign in this way; an IMIS
 * employee with the `employee` code signs in as an applicant (an internal one).
 *
 * Board designations (chairperson, secretariat, …) stay in this system's own
 * HRMPSB composition; IMIS only decides who may sign in and as which role.
 */
class ImisSsoController extends Controller
{
    /**
     * IMIS role code => role held here. `employee` (also what a missing code means)
     * is an internal applicant: an IMIS user with no staff role who applies like
     * anyone else. The plain `applicant` code is still not accepted; external
     * applicants are not in IMIS.
     */
    private const ROLES = ['admin' => 'admin', 'hrmpsb' => 'hrmpsb', 'employee' => 'applicant'];

    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->input('sso_token');
        $url = config('services.imis.sso_url');

        if (! $url || ! is_string($token) || $token === '') {
            return $this->refuse('Single sign-on is not available. Please sign in below.', 422);
        }

        try {
            $response = Http::asForm()->timeout(10)->post($url, [
                'token' => $token,
                'audience' => config('services.imis.audience'),
            ]);
        } catch (Throwable $e) {
            Log::warning('IMIS sign-in failed', ['message' => $e->getMessage()]);

            return $this->refuse('IMIS could not be reached. Please sign in below.', 502);
        }

        $imis = $response->successful() && $response->json('success') === true ? $response->json('user') : null;

        if (! is_array($imis) || ! is_int($imis['id'] ?? null) || ! is_string($imis['email'] ?? null) || $imis['email'] === '') {
            // 422, not 401: the shared axios interceptor treats every 401 as an expired session.
            return $this->refuse('Your IMIS sign-in could not be used here. Please sign in below.', 422);
        }

        // No role code means IMIS let them launch the app as a guest (it is open to all
        // employees): an internal applicant, same as the explicit `employee` code.
        $code = $imis['role_code'] ?? 'employee';

        if (! is_string($code) || ! array_key_exists($code, self::ROLES)) {
            return $this->refuse('You have not been given a role in the Recruitment Portal in IMIS. Please contact your administrator.', 403);
        }

        $role = self::ROLES[$code];
        $user = $this->account($imis, $role);

        if ($user === false) {
            return $this->refuse('Your account clashes with an existing Recruitment Portal account. Please contact your administrator.', 409);
        }

        $user ??= new User;

        $user->forceFill([
            'imis_user_id' => $imis['id'],
            'first_name' => trim((string) ($imis['fname'] ?? '')) ?: $imis['username'] ?? 'Staff',
            'last_name' => trim((string) ($imis['lname'] ?? '')),
            'middle_name' => trim((string) ($imis['mname'] ?? '')) ?: null,
            'email' => $imis['email'],
            'role' => $role,
            // Keeps the current photo when IMIS has none to give.
            'google_avatar' => $this->photoUrl($imis) ?? $user->google_avatar,
            'deactivated_at' => null,
            'last_login_at' => now(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        if (! $user->exists) {
            // Never used to sign in: staff come through IMIS. Unguessable all the same.
            $user->forceFill(['password' => Str::random(64), 'has_password' => false]);
        }

        $user->save();

        Auth::setUser($user);
        AuditLog::record('login_imis', $user);

        // The same lifetime as a password sign-in without "remember me".
        $newToken = $user->createToken('api-token', ['*'], now()->addHours(2));

        // So signing out of IMIS can revoke this token (ImisSsoLogoutController).
        if (is_string($imis['sid'] ?? null) && strlen($imis['sid']) === 64) {
            try {
                $newToken->accessToken->forceFill(['imis_sid_hash' => hash('sha256', $imis['sid'])])->save();
            } catch (Throwable $e) {
                // Only the sign-out link is lost; the person still signs in.
                Log::warning('IMIS session could not be recorded', ['message' => $e->getMessage()]);
            }
        }

        return response()->json(['token' => $newToken->plainTextToken, 'user' => UserResource::make($user)]);
    }

    /**
     * The account for this IMIS user: by its IMIS id, else an unlinked
     * non-applicant account with the same email. Null when there is none (one is
     * created); false when the email belongs to an applicant who must not be
     * promoted to staff by a matching address, or to an account linked to someone else.
     */
    private function account(array $imis, string $role): User|false|null
    {
        if ($linked = User::where('imis_user_id', $imis['id'])->first()) {
            return $linked;
        }

        $match = User::where('email', $imis['email'])->first();

        if ($match === null) {
            return null;
        }

        if ($match->imis_user_id !== null) {
            return false;
        }

        // Staff: only an existing staff account. Employee: also an applicant account
        // the person registered themselves, but only once its email was verified, so
        // an address nobody proved they own can't be claimed through IMIS.
        if ($role === 'applicant') {
            return $match->role === 'applicant' && $match->email_verified_at !== null ? $match : false;
        }

        return $match->role !== 'applicant' ? $match : false;
    }

    /**
     * The person's IMIS photo (a public URL on the IMIS host), kept in
     * `google_avatar`, which both the header and the profile already show.
     * Only an http(s) URL that fits the column; IMIS is the only writer for
     * linked accounts, so there is no Google avatar to clobber.
     */
    private function photoUrl(array $imis): ?string
    {
        $url = $imis['photo_url'] ?? null;

        return is_string($url) && strlen($url) <= 255 && preg_match('#^https?://#i', $url) ? $url : null;
    }

    private function refuse(string $message, int $status): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
