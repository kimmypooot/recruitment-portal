<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyRecusal;
use App\Services\AuditLog;
use App\Services\ConflictOfInterestService;
use App\Services\HrmpsbCompositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin-side record of who sits a vacancy out. Members are recused
 * automatically when they apply; this is where a declared relationship with
 * a candidate (or any other conflict) is recorded.
 */
class VacancyRecusalController extends Controller
{
    public function index(Vacancy $vacancy): JsonResponse
    {
        $rows = VacancyRecusal::with('user:id,first_name,last_name,middle_name,suffix,email', 'recordedBy:id,first_name,last_name')
            ->where('vacancy_id', $vacancy->id)
            ->latest()
            ->get()
            ->map(fn (VacancyRecusal $r) => [
                'id' => $r->id,
                'user_id' => $r->user_id,
                'name' => $r->user?->full_name,
                'reason' => $r->reason,
                'notes' => $r->notes,
                'recorded_by' => $r->recordedBy?->full_name,
                'created_at' => $r->created_at,
            ]);

        return response()->json(['data' => $rows, 'quorum' => app(HrmpsbCompositionService::class)->quorumStatus($vacancy)]);
    }

    public function store(Request $request, Vacancy $vacancy, ConflictOfInterestService $service): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'reason' => ['required', Rule::in(['relative', 'other'])], // 'applicant' is only ever automatic
            'notes' => 'nullable|string|max:1000',
        ]);

        $recusal = $service->recuse($vacancy, User::findOrFail($data['user_id']), $data['reason'], $data['notes'] ?? null, $request->user());

        return response()->json([
            'data' => $recusal,
            'quorum' => app(HrmpsbCompositionService::class)->quorumStatus($vacancy),
        ], $recusal->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Vacancy $vacancy, VacancyRecusal $recusal): JsonResponse
    {
        abort_unless($recusal->vacancy_id === $vacancy->id, 404);

        // Applying is what recused them; lifting it would not lift their application.
        if ($recusal->reason === 'applicant') {
            return response()->json(['message' => 'This recusal is automatic because the person applied for the vacancy.'], 422);
        }

        AuditLog::record('vacancy_recusal_removed', $recusal);
        $recusal->delete();

        return response()->json(['message' => 'Recusal removed.']);
    }
}
