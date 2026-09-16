<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLog;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::select('id', 'first_name', 'last_name', 'middle_name', 'suffix', 'email', 'role', 'photo_path', 'google_avatar', 'created_at')
            ->with('applicantProfile:user_id,photo_path');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(20);

        $users->getCollection()->transform(function ($user) {
            $path = $user->photo_path ?? $user->applicantProfile?->photo_path;
            $user->photo_url = $path && Storage::disk('public')->exists($path)
                ? Storage::url($path)
                : $user->google_avatar;
            unset($user->applicantProfile);

            return $user;
        });

        return response()->json($users);
    }

    private const ALLOWED_ROLES = ['applicant', 'hrmpsb', 'admin'];

    /**
     * `admin-access` (see EnsureAdminAccess/User::canAccessAdminModule) admits
     * two different trust levels: a true admin, and an hrmpsb user with a
     * delegated secretariat/hr-chief designation. Both reach every method
     * below equally, but only a true admin may hand out the `admin` role
     * itself — otherwise a delegated user could use this endpoint to promote
     * themselves (or anyone) past what their designation was ever meant to
     * grant.
     */
    private function canGrantAdminRole(Request $request): bool
    {
        return $request->user()->role === 'admin';
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'suffix' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(self::ALLOWED_ROLES)],
        ]);

        if ($data['role'] === 'admin' && ! $this->canGrantAdminRole($request)) {
            return response()->json(['message' => 'Only an administrator can create an admin account.'], 403);
        }

        $user = User::create($data);

        AuditLog::record('user_created', $user);

        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->role,
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'suffix' => 'nullable|string|max:20',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', 'required', Rule::in(self::ALLOWED_ROLES)],
            'password' => 'sometimes|required|string|min:8|confirmed',
        ]);

        // Role tampering in either direction — granting admin, or touching an
        // existing admin's role at all — is restricted to true admins, same
        // as store() above.
        $touchesAdminRole = (isset($data['role']) && $data['role'] === 'admin') || $user->role === 'admin';
        if ($touchesAdminRole && ! $this->canGrantAdminRole($request)) {
            return response()->json(['message' => 'Only an administrator can grant or modify an admin account.'], 403);
        }

        if ($request->has('password')) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        AuditLog::record('user_updated', $user);

        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->role,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->role === 'admin' && ! $this->canGrantAdminRole($request)) {
            return response()->json(['message' => 'Only an administrator can delete an admin account.'], 403);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                return response()->json([
                    'message' => 'This user cannot be deleted because they have produced evaluation, decision, or form records in the recruitment pipeline. Deactivate the account instead.',
                ], 422);
            }

            throw $e;
        }

        AuditLog::record('user_deleted', $user);

        return response()->json(['message' => 'User deleted.']);
    }

    public function uploadPhoto(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'photo' => 'required|file|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:3072',
        ]);

        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
        }

        $user->photo_path = $request->file('photo')->store("profile-photos/{$user->id}", 'public');
        $user->save();

        AuditLog::record('user_photo_updated', $user);

        return response()->json(['photo_url' => Storage::url($user->photo_path)]);
    }
}
