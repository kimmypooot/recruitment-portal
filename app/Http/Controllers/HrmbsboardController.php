<?php

namespace App\Http\Controllers;

use App\Models\HrmbsboardComposition;
use App\Models\User;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HrmbsboardController extends Controller
{
    public function compositions(): JsonResponse
    {
        $compositions = HrmbsboardComposition::with(['user:id,name,email,role', 'assignedBy:id,name'])
            ->orderBy('hrmpsb_role')
            ->get();

        return response()->json([
            'compositions' => $compositions,
            'roles'        => HrmbsboardComposition::ROLES,
        ]);
    }

    public function assign(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'hrmpsb_role' => ['required', Rule::in(array_keys(HrmbsboardComposition::ROLES))],
            'member_type' => 'required|in:principal,alternate',
        ]);

        $composition = HrmbsboardComposition::updateOrCreate(
            [
                'user_id'     => $data['user_id'],
                'hrmpsb_role' => $data['hrmpsb_role'],
            ],
            [
                'member_type' => $data['member_type'],
                'is_active'   => true,
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ]
        );

        // Auto-set system role so the user lands on the HRMPSB portal on login
        $systemRole = $data['hrmpsb_role'] === 'secretariat'
            ? 'hrmpsb-secretariat'
            : 'hrmpsb-member';

        User::where('id', $data['user_id'])
            ->whereNotIn('role', ['admin', 'hr-manager'])
            ->update(['role' => $systemRole]);

        AuditLog::record("hrmpsb_assigned:{$data['hrmpsb_role']}", $composition);

        return response()->json($composition->load('user:id,name,email,role'), 201);
    }

    public function remove(HrmbsboardComposition $composition): JsonResponse
    {
        AuditLog::record("hrmpsb_removed:{$composition->hrmpsb_role}", $composition);

        $composition->delete();

        return response()->json(['message' => 'Member removed from HRMPSB.']);
    }

    public function toggleActive(HrmbsboardComposition $composition): JsonResponse
    {
        $composition->update(['is_active' => !$composition->is_active]);

        AuditLog::record("hrmpsb_toggled_active:{$composition->hrmpsb_role}", $composition);

        return response()->json($composition->fresh());
    }

    public function toggleType(HrmbsboardComposition $composition): JsonResponse
    {
        $composition->update([
            'member_type' => $composition->member_type === 'principal' ? 'alternate' : 'principal',
        ]);

        AuditLog::record("hrmpsb_type_toggled:{$composition->hrmpsb_role}→{$composition->member_type}", $composition);

        return response()->json($composition->fresh());
    }

    public function myRole(Request $request): JsonResponse
    {
        $composition = HrmbsboardComposition::with('user:id,name,email')
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        return response()->json([
            'composition' => $composition,
            'roles'       => HrmbsboardComposition::ROLES,
            'user'        => $request->user()->only('id', 'name', 'email', 'role'),
        ]);
    }
}
