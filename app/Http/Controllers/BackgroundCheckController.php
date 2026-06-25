<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BackgroundCheck;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BackgroundCheckController extends Controller
{
    private function isSecretariat(int $userId): bool
    {
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('hrmpsb_role', 'secretariat')
            ->where('is_active', true)
            ->exists();
    }

    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $isMember = in_array($user->role, ['admin', 'hr-manager', 'hrmpsb-secretariat'])
            || HrmbsboardComposition::where('user_id', $user->id)
                ->where('is_active', true)
                ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $isSecretariat = $this->isSecretariat($user->id)
            || in_array($user->role, ['admin', 'hr-manager']);

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->with(['applicant:id,first_name,last_name,middle_name', 'backgroundChecks'])
            ->orderBy('id')
            ->get();

        $locked = BackgroundCheck::whereIn(
            'application_id',
            $applications->pluck('id')
        )->whereNotNull('locked_at')->exists();

        $applications->each(function ($app) use ($user, $isSecretariat) {
            $app->my_check = $app->backgroundChecks
                ->where('checked_by', $user->id)
                ->first();

            if ($isSecretariat) {
                $app->all_checks = $app->backgroundChecks
                    ->load('checkedBy:id,name');
            }
        });

        return response()->json([
            'vacancy'       => $vacancy->only(
                'id', 'position_title', 'item_number', 'salary_grade',
                'place_of_assignment', 'status', 'published_at'
            ),
            'applications'  => $applications,
            'is_secretariat' => $isSecretariat,
            'locked'        => $locked,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $isMember = HrmbsboardComposition::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if (!$isMember && !in_array($user->role, ['admin', 'hr-manager', 'hrmpsb-secretariat'])) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $data = $request->validate([
            'application_id'         => 'required|exists:applications,id',
            'employment_verified'    => 'nullable|boolean',
            'education_verified'     => 'nullable|boolean',
            'character_ref_verified' => 'nullable|boolean',
            'nbi_clearance'          => 'nullable|boolean',
            'background_result'      => 'nullable|in:clear,minor_issues,significant_issues',
            'remarks'                => 'nullable|string|max:2000',
        ]);

        $existing = BackgroundCheck::where('application_id', $data['application_id'])
            ->where('checked_by', $user->id)
            ->first();

        if ($existing?->isLocked()) {
            return response()->json(['message' => 'Background checks are locked and cannot be modified.'], 422);
        }

        $check = BackgroundCheck::updateOrCreate(
            [
                'application_id' => $data['application_id'],
                'checked_by'     => $user->id,
            ],
            [
                'employment_verified'    => $data['employment_verified'] ?? null,
                'education_verified'     => $data['education_verified'] ?? null,
                'character_ref_verified' => $data['character_ref_verified'] ?? null,
                'nbi_clearance'          => $data['nbi_clearance'] ?? null,
                'background_result'      => $data['background_result'] ?? null,
                'remarks'                => $data['remarks'] ?? null,
                'checked_at'             => now(),
            ]
        );

        AuditLog::record('background_check_submitted', $check);

        return response()->json($check, 201);
    }

    public function lock(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (!$this->isSecretariat($user->id) && !in_array($user->role, ['admin', 'hr-manager'])) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can lock background checks.'], 403);
        }

        $applicationIds = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->pluck('id');

        $count = BackgroundCheck::whereIn('application_id', $applicationIds)
            ->whereNull('locked_at')
            ->update(['locked_at' => now()]);

        foreach ($applicationIds as $appId) {
            $checks = BackgroundCheck::where('application_id', $appId)->get();

            if ($checks->isEmpty()) {
                continue;
            }

            $allClear = $checks->every(fn ($c) =>
                $c->employment_verified && $c->education_verified
                && $c->character_ref_verified && $c->nbi_clearance
                && $c->background_result === 'clear'
            );

            Application::find($appId)?->update([
                'status' => $allClear ? 'passed' : 'failed',
            ]);
        }

        AuditLog::record('background_checks_locked', $vacancy);

        return response()->json([
            'message'      => 'Background checks locked successfully.',
            'locked_count' => $count,
        ]);
    }
}
