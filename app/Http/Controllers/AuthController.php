<?php

namespace App\Http\Controllers;

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
            'last_name'   => 'required|string|max:100',
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'suffix'      => 'nullable|string|max:20',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|string|min:8|confirmed',
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
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    public function googleRedirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name'              => $googleUser->getName(),
                'password'          => Hash::make(Str::random(32)),
                'role'              => 'applicant',
                'email_verified_at' => now(),
            ]
        );

        $token = $user->createToken('api-token')->plainTextToken;

        return redirect('/?token=' . $token);
    }
}
