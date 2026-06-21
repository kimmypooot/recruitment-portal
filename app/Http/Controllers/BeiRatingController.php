<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BeiRating;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeiRatingController extends Controller
{
    private function getComposition(int $vacancyId, int $userId): ?HrmbsboardComposition
    {
        return HrmbsboardComposition::where('vacancy_id', $vacancyId)
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }

    private function isSecretariat(int $vacancyId, int $userId): bool
    {
        return HrmbsboardComposition::where('vacancy_id', $vacancyId)
            ->where('user_id', $userId)
            ->where('hrmpsb_role', 'secretariat')
            ->where('is_active', true)
            ->exists();
    }

    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $canAdmin = in_array($user->role, ['admin', 'hr-manager']);
        $isSecretariat = $canAdmin || $this->isSecretariat($vacancy->id, $user->id);
        $composition = $this->getComposition($vacancy->id, $user->id);

        if (!$isSecretariat && !$composition) {
            return response()->json(['message' => 'You are not assigned to this vacancy\'s HRMPSB.'], 403);
        }

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->with('anonymizationToken')
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->get()
            ->map(function ($app) use ($user, $isSecretariat) {
                $token = $app->anonymizationToken;
                $isUnmasked = $token?->isUnmasked();

                $base = [
                    'id'       => $app->id,
                    'token'    => $token?->token ?? 'NO-TOKEN',
                    'unmasked' => $isUnmasked,
                    'name'     => $isUnmasked ? trim($app->applicant?->first_name . ' ' . $app->applicant?->last_name) : null,
                ];

                if ($isSecretariat) {
                    $base['all_ratings'] = BeiRating::where('application_id', $app->id)
                        ->with('evaluator:id,name')
                        ->get()
                        ->map(fn ($r) => [
                            'evaluator'         => $r->evaluator?->name,
                            'competency_scores' => $r->competency_scores,
                            'total_rating'      => $r->total_rating,
                            'locked'            => $r->isLocked(),
                        ]);
                } else {
                    $base['my_rating'] = BeiRating::where('application_id', $app->id)
                        ->where('evaluator_id', $user->id)
                        ->first();
                }

                return $base;
            });

        return response()->json([
            'vacancy'        => $vacancy->only('id', 'position_title', 'status'),
            'competencies'   => BeiRating::COMPETENCIES,
            'applications'   => $applications,
            'is_secretariat' => $isSecretariat,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $competencyKeys = implode(',', array_keys(BeiRating::COMPETENCIES));

        $data = $request->validate([
            'application_id'    => 'required|exists:applications,id',
            'competency_scores' => 'required|array',
            'competency_scores.*' => 'required|integer|min:1|max:5',
            'remarks'           => 'nullable|string|max:1000',
        ]);

        $application = Application::with('vacancy')->findOrFail($data['application_id']);
        $user = $request->user();

        $composition = $this->getComposition($application->vacancy_id, $user->id);
        if (!$composition && !in_array($user->role, ['admin', 'hr-manager'])) {
            return response()->json(['message' => 'You are not assigned to this vacancy\'s HRMPSB.'], 403);
        }

        $existing = BeiRating::where('application_id', $data['application_id'])
            ->where('evaluator_id', $user->id)
            ->first();

        if ($existing?->isLocked()) {
            return response()->json(['message' => 'BEI ratings are locked and cannot be modified.'], 422);
        }

        $rating = BeiRating::updateOrCreate(
            ['application_id' => $data['application_id'], 'evaluator_id' => $user->id],
            [
                'competency_scores' => $data['competency_scores'],
                'remarks'           => $data['remarks'] ?? null,
                'rated_at'          => now(),
            ]
        );

        // Compute and store total rating
        $rating->update(['total_rating' => $rating->computeTotalRating()]);

        AuditLog::record('bei_rating_submitted', $application);

        return response()->json($rating->fresh(), 201);
    }

    public function lock(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (!$this->isSecretariat($vacancy->id, $user->id) && !in_array($user->role, ['admin', 'hr-manager'])) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can lock BEI ratings.'], 403);
        }

        $applicationIds = Application::where('vacancy_id', $vacancy->id)->pluck('id');

        $count = BeiRating::whereIn('application_id', $applicationIds)
            ->whereNull('locked_at')
            ->update(['locked_at' => now()]);

        AuditLog::record('bei_ratings_locked', $vacancy);

        return response()->json([
            'message'      => 'BEI ratings locked successfully.',
            'locked_count' => $count,
        ]);
    }
}
