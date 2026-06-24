<?php

namespace App\Http\Controllers;

use App\Models\PrivacyConsent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user  = $request->user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'last_name'              => 'required|string|max:100',
            'first_name'             => 'required|string|max:100',
            'middle_name'            => 'nullable|string|max:100',
            'suffix'                 => 'nullable|string|max:20',
            'email'                  => 'required|email|unique:users',
            'password'               => 'required|string|min:8|confirmed',
            'privacy_policy_version' => 'required|string|max:20',
        ]);

        $name = trim(
            $data['first_name'] . ' ' .
            ($data['middle_name'] ? $data['middle_name'] . ' ' : '') .
            $data['last_name'] .
            ($data['suffix'] ? ', ' . $data['suffix'] : '')
        );

        $user  = User::create([
            'name'     => $name,
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'applicant',
        ]);

        PrivacyConsent::record($user, $data['privacy_policy_version'], $request);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    // Re-consent endpoint: called when the privacy policy version changes and
    // existing logged-in users must acknowledge the updated policy.
    public function recordConsent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'privacy_policy_version' => 'required|string|max:20',
        ]);

        PrivacyConsent::record($request->user(), $data['privacy_policy_version'], $request);

        return response()->json(['message' => 'Consent recorded.']);
    }

    // ── Google OAuth: Login flow ──────────────────────────────────────────

    public function googleRedirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        try {
            // 1. Try linking flow first (initiated by an authenticated user from profile)
            $state = $request->input('state');
            if ($state) {
                $linkData = json_decode(decrypt($state), true);
                if (
                    $linkData &&
                    ($linkData['action'] ?? null) === 'link' &&
                    ($linkData['user_id'] ?? null) &&
                    ($linkData['expires'] ?? 0) > now()->timestamp
                ) {
                    return $this->handleGoogleLink($linkData['user_id']);
                }
            }

            // 2. Login flow — fetch Google user
            $googleUser = Socialite::driver('google')->stateless()->user();

            // 3. Check if google_id already exists
            $existing = User::where('google_id', $googleUser->getId())->first();
            if ($existing) {
                return $this->redirectWithToken($existing);
            }

            // 4. Check if email conflicts with an existing password account
            $conflict = User::where('email', $googleUser->getEmail())->whereNull('google_id')->first();
            if ($conflict) {
                return redirect()->route('auth.google.callback-handler', ['error' => 'email_exists']);
            }

            // 5. Create new user
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'password'          => Hash::make(Str::random(32)),
                'role'              => 'applicant',
                'email_verified_at' => now(),
                'google_id'         => $googleUser->getId(),
                'google_avatar'     => $googleUser->getAvatar(),
            ]);

            return $this->redirectWithToken($user);
        } catch (\Exception $e) {
            return redirect()->route('auth.google.callback-handler', ['error' => 'auth_failed']);
        }
    }

    // ── Google OAuth: Account linking flow ────────────────────────────────

    /**
     * API endpoint: Initiate Google account linking.
     * Authenticated user calls this, gets back a Google redirect URL.
     */
    public function googleLinkApi(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->google_id) {
            return response()->json(['message' => 'Google account already linked.'], 422);
        }

        $state = encrypt(json_encode([
            'action'  => 'link',
            'user_id' => $user->id,
            'expires' => now()->addMinutes(10)->timestamp,
        ]));

        $redirectUrl = Socialite::driver('google')
            ->with(['state' => $state])
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['redirect_url' => $redirectUrl]);
    }

    /**
     * Handle the linking callback — called from googleCallback().
     */
    private function handleGoogleLink(int $userId): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::find($userId);
            if (!$user) {
                return redirect()->route('auth.google.callback-handler', ['error' => 'link_user_not_found']);
            }

            $taken = User::where('google_id', $googleUser->getId())->where('id', '!=', $userId)->first();
            if ($taken) {
                return redirect()->route('auth.google.callback-handler', ['error' => 'link_already_taken']);
            }

            $user->update([
                'google_id'     => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
            ]);

            $userData = base64_encode(json_encode(
                $user->only(['id', 'name', 'email', 'role', 'google_id', 'google_avatar'])
            ));

            return redirect()->route('auth.google.callback-handler', [
                'link_success' => 1,
                'user'         => $userData,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('auth.google.callback-handler', ['error' => 'link_failed']);
        }
    }

    /**
     * Unlink Google account from the authenticated user.
     */
    public function googleUnlink(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'google_id'     => null,
            'google_avatar' => null,
        ]);

        return response()->json(['message' => 'Google account disconnected.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function redirectWithToken(User $user): RedirectResponse
    {
        $token = $user->createToken('api-token')->plainTextToken;
        $userData = base64_encode(json_encode(
            $user->only(['id', 'name', 'email', 'role', 'google_id', 'google_avatar'])
        ));
        return redirect()->route('auth.google.callback-handler', [
            'token' => $token,
            'user'  => $userData,
        ]);
    }
}
