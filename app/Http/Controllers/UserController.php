<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::select('id', 'name', 'email', 'role', 'created_at');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(20);

        return response()->json($users);
    }

    private const ALLOWED_ROLES = [
        'applicant', 'hr-officer', 'hr-manager', 'admin',
        'hrmpsb-member', 'hrmpsb-secretariat', 'appointing-authority',
    ];

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
            'name'  => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'  => ['sometimes', 'required', Rule::in(self::ALLOWED_ROLES)],
        ]);

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
