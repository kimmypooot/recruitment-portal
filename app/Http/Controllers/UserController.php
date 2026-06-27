<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::select('id', 'name', 'email', 'role', 'created_at')
            ->with('applicantProfile:user_id,photo_path');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(20);

        $users->getCollection()->transform(function ($user) {
            $path = $user->applicantProfile?->photo_path;
            $user->photo_url = $path && Storage::disk('public')->exists($path)
                ? Storage::url($path)
                : null;
            unset($user->applicantProfile);
            return $user;
        });

        return response()->json($users);
    }

    private const ALLOWED_ROLES = ['applicant', 'hrmpsb', 'admin'];

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => ['required', Rule::in(self::ALLOWED_ROLES)],
        ]);

        $user = User::create($data);

        AuditLog::record('user_created', $user);

        return response()->json($user->only('id', 'name', 'email', 'role'), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->only('id', 'name', 'email', 'role', 'created_at'));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'email'    => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'     => ['sometimes', 'required', Rule::in(self::ALLOWED_ROLES)],
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        if ($request->has('password')) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        AuditLog::record('user_updated', $user);

        return response()->json($user->only('id', 'name', 'email', 'role'));
    }

    public function destroy(User $user): JsonResponse
    {
        AuditLog::record('user_deleted', $user);

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
