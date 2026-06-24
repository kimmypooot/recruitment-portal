<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BeiRating;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Models\VacancyCompetency;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeiRatingController extends Controller
{
    use \App\Traits\FormatsApplicantName;
    private function getComposition(int $userId): ?HrmbsboardComposition
    {
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }

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

        $canAdmin = in_array($user->role, ['admin', 'hr-manager']);
        $isSecretariat = $canAdmin || $this->isSecretariat($user->id);
        $composition = $this->getComposition($user->id);

        if (!$isSecretariat && !$composition) {
            return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
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
                    'name'     => $isUnmasked ? $this->formatApplicantName($app->applicant) : null,
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

        $vacancyCompetencies = VacancyCompetency::where('vacancy_id', $vacancy->id)
            ->with('competency')
            ->get()
            ->sortBy(fn ($vc) => [$vc->competency?->competency_group, $vc->competency?->sort_order])
            ->mapWithKeys(fn ($vc) => [
                $vc->competency_key => [
                    'name'        => $vc->competency?->competency_name ?? $vc->competency_key,
                    'group'       => $vc->competency?->competency_group ?? 'Core',
                    'level'       => $vc->competency_level,
                    'description' => $vc->competency?->description,
                ],
            ]);

        return response()->json([
            'vacancy'        => $vacancy->only(
                'id', 'position_title', 'item_number', 'salary_grade',
                'place_of_assignment', 'status', 'published_at'
            ),
            'competencies'   => $vacancyCompetencies,
            'applications'   => $applications,
            'is_secretariat' => $isSecretariat,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id'      => 'required|exists:applications,id',
            'competency_scores'   => 'required|array',
            'competency_scores.*' => 'required|integer|min:1|max:5',
            'remarks'             => 'nullable|string|max:1000',
        ]);

        $application = Application::with('vacancy')->findOrFail($data['application_id']);
        $user = $request->user();

        // Ensure submitted keys match the vacancy's assigned competencies
        $validKeys = VacancyCompetency::where('vacancy_id', $application->vacancy_id)
            ->pluck('competency_key')
            ->toArray();

        $unknownKeys = array_diff(array_keys($data['competency_scores']), $validKeys);
        if (!empty($unknownKeys)) {
            return response()->json([
                'message' => 'One or more competency scores do not match this vacancy\'s assigned competencies.',
            ], 422);
        }

        $composition = $this->getComposition($user->id);
        if (!$composition && !in_array($user->role, ['admin', 'hr-manager'])) {
            return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
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

        if (!$this->isSecretariat($user->id) && !in_array($user->role, ['admin', 'hr-manager'])) {
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
