<?php

namespace App\Http\Controllers;

use App\Models\HrmbsboardComposition;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HrmbsboardController extends Controller
{
    public function compositions(Vacancy $vacancy): JsonResponse
    {
        $compositions = HrmbsboardComposition::where('vacancy_id', $vacancy->id)
            ->with(['user:id,name,email,role', 'assignedBy:id,name'])
            ->orderBy('hrmpsb_role')
            ->get();

        return response()->json([
            'vacancy'      => $vacancy->only('id', 'position_title', 'status'),
            'compositions' => $compositions,
            'roles'        => HrmbsboardComposition::ROLES,
        ]);
    }

    public function assign(Request $request, Vacancy $vacancy): JsonResponse
    {
        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'hrmpsb_role' => ['required', Rule::in(array_keys(HrmbsboardComposition::ROLES))],
            'member_type' => 'required|in:principal,alternate',
        ]);

        $composition = HrmbsboardComposition::updateOrCreate(
            [
                'vacancy_id'  => $vacancy->id,
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

        // Auto-set system role so user lands on the HRMPSB portal on login
        $systemRole = $data['hrmpsb_role'] === 'secretariat'
            ? 'hrmpsb-secretariat'
            : 'hrmpsb-member';

        User::where('id', $data['user_id'])
            ->whereNotIn('role', ['admin', 'hr-manager'])
            ->update(['role' => $systemRole]);

        AuditLog::record("hrmpsb_assigned:{$data['hrmpsb_role']}", $vacancy);

        return response()->json($composition->load('user:id,name,email,role'), 201);
    }

    public function remove(HrmbsboardComposition $composition): JsonResponse
    {
        AuditLog::record("hrmpsb_removed:{$composition->hrmpsb_role}", $composition->vacancy);

        $composition->delete();

        return response()->json(['message' => 'Member removed from HRMPSB.']);
    }

    public function toggleType(HrmbsboardComposition $composition): JsonResponse
    {
        $composition->update([
            'member_type' => $composition->member_type === 'principal' ? 'alternate' : 'principal',
        ]);

        AuditLog::record("hrmpsb_type_toggled:{$composition->hrmpsb_role}→{$composition->member_type}", $composition->vacancy);

        return response()->json($composition->fresh());
    }

    public function vacanciesWithCompositions(Request $request): JsonResponse
    {
        $vacancies = Vacancy::whereHas('hrmbsboardCompositions')
            ->with(['hrmbsboardCompositions' => function ($q) {
                $q->with('user:id,name,email');
            }])
            ->latest('published_at')
            ->paginate(15);

        return response()->json($vacancies);
    }

    // For HRMPSB members: returns only their own assignments
    public function myAssignments(Request $request): JsonResponse
    {
        $compositions = HrmbsboardComposition::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with(['vacancy:id,position_title,status,deadline_at,published_at'])
            ->orderByDesc('assigned_at')
            ->get();

        return response()->json([
            'assignments' => $compositions,
            'roles'       => HrmbsboardComposition::ROLES,
        ]);
    }
}
