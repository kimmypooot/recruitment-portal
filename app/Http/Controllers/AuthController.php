<?php

namespace App\Http\Controllers;

use App\Models\PrivacyConsent;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        if (!Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user  = $request->user();
        $expiresAt = $request->boolean('remember') ? null : now()->addHours(2);
        $token = $user->createToken('api-token', ['*'], $expiresAt)->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $isGoogleOnly = (bool) $user->google_id;

        $rules = [
            'password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()],
        ];

        if (!$isGoogleOnly) {
            $rules['current_password'] = 'required|string';
        }

        $request->validate($rules);

        if (!$isGoogleOnly && !Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'The current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => $isGoogleOnly ? 'Password set successfully. You can now sign in with your email and password.' : 'Password changed successfully.']);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'last_name'              => 'required|string|max:100',
            'first_name'             => 'required|string|max:100',
            'middle_name'            => 'nullable|string|max:100',
            'suffix'                 => 'nullable|string|max:20',
            'email'                  => 'required|email|unique:users',
            'password'               => [
                'required', 'string', 'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
            ],
            'privacy_policy_version' => 'required|string|max:20',
        ], [
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        $middleName = $data['middle_name'] ?? null;
        $suffix     = $data['suffix'] ?? null;

        $user = DB::transaction(function () use ($data, $middleName, $suffix, $request) {
            // Lock the users table to prevent race conditions on duplicate checks
            $existing = User::lockForUpdate()
                ->whereRaw('LOWER(first_name) = ? AND LOWER(last_name) = ?', [
                    strtolower(trim($data['first_name'])),
                    strtolower(trim($data['last_name'])),
                ])
                ->when($data['middle_name'] ?? null, fn ($q, $m) =>
                    $q->whereRaw('LOWER(middle_name) = ?', [strtolower(trim($m))]
                ))
                ->first();

            if ($existing) {
                abort(409, 'An account with this name already exists. Please log in or contact support if you need help recovering your account.');
            }

            $user = User::create([
                'first_name'  => trim($data['first_name']),
                'last_name'   => trim($data['last_name']),
                'middle_name' => $middleName ? trim($middleName) : null,
                'suffix'      => $suffix ? trim($suffix) : null,
                'email'       => $data['email'],
                'password'    => Hash::make($data['password']),
                'role'        => 'applicant',
            ]);

            PrivacyConsent::record($user, $data['privacy_policy_version'], $request);

            return $user;
        });

        $token = $user->createToken('api-token', ['*'], null)->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    // Re-consent endpoint

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

    // ── Email verification ────────────────────────────────────────────────

    public function verifyEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended('/applicant/dashboard');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended('/applicant/dashboard')
            ->with('message', 'Email verified successfully.');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended('/applicant/dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent.');
    }

    // ── Google OAuth: Login flow ──────────────────────────────────────────

    public function googleRedirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        try {
            // 1. Try linking flow first (initiated by an authenticated user from profile).
            // The link flow passes a custom encrypted state; the login flow (stateless) sends none.
            $state = $request->input('state');
            if ($state) {
                try {
                    $linkData = json_decode(decrypt($state), true);
                    if (
                        $linkData &&
                        ($linkData['action'] ?? null) === 'link' &&
                        ($linkData['user_id'] ?? null) &&
                        ($linkData['expires'] ?? 0) > now()->timestamp
                    ) {
                        return $this->handleGoogleLink($linkData['user_id']);
                    }
                } catch (DecryptException $e) {
                    // Unrecognised state — fall through to login flow
                }
            }

            // 2. Login flow — fetch Google user
            $googleUser = Socialite::driver('google')->stateless()->user();

            // 3. Check if google_id already exists
            $existing = User::where('google_id', $googleUser->getId())->first();
            if ($existing) {
                $existing->update([
                    'google_avatar' => $googleUser->getAvatar(),
                ]);
                return $this->redirectWithToken($existing->fresh());
            }

            // 4. Check if email already in use by any user (password or Google-linked)
            $existingEmail = User::where('email', $googleUser->getEmail())->first();
            if ($existingEmail) {
                return redirect()->route('auth.google.callback-handler', ['error' => 'email_exists']);
            }

            // 5. Parse Google full name into components
            $fullName = $googleUser->getName();
            $nameParts = preg_split('/\s+/', trim($fullName));
            $gFirstName = $nameParts[0] ?? '';
            $gLastName = count($nameParts) > 1 ? array_pop($nameParts) : '';
            $gMiddleName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : null;

            // 6. Check for name-based duplicate
            $duplicate = User::lockForUpdate()
                ->whereRaw('LOWER(first_name) = ? AND LOWER(last_name) = ?', [
                    strtolower($gFirstName),
                    strtolower($gLastName),
                ])
                ->when($gMiddleName, fn ($q, $m) =>
                    $q->whereRaw('LOWER(middle_name) = ?', [strtolower($m)]
                ))
                ->first();

            if ($duplicate) {
                return redirect()->route('auth.google.callback-handler', ['error' => 'name_exists']);
            }

            // 7. Create new user
            $user = User::create([
                'first_name'        => $gFirstName,
                'last_name'         => $gLastName,
                'middle_name'       => $gMiddleName,
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

            $user = $user->fresh();

            $userData = base64_encode(json_encode([
                'id'          => $user->id,
                'first_name'  => $user->first_name,
                'last_name'   => $user->last_name,
                'middle_name' => $user->middle_name,
                'suffix'      => $user->suffix,
                'full_name'   => $user->full_name,
                'email'       => $user->email,
                'role'        => $user->role,
                'google_id'   => $user->google_id,
                'google_avatar' => $user->google_avatar,
            ]));

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
        $token = $user->createToken('api-token', ['*'], null)->plainTextToken;
        $userData = base64_encode(json_encode([
            'id'          => $user->id,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'middle_name' => $user->middle_name,
            'suffix'      => $user->suffix,
            'full_name'   => $user->full_name,
            'email'       => $user->email,
            'role'        => $user->role,
            'google_id'   => $user->google_id,
            'google_avatar' => $user->google_avatar,
        ]));
        return redirect()->route('auth.google.callback-handler', [
            'token' => $token,
            'user'  => $userData,
        ]);
    }
}
