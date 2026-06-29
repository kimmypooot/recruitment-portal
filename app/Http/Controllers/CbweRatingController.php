<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\CbweRating;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Models\VacancyCompetency;
use App\Services\AuditLog;
use App\Traits\FormatsApplicantName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CbweRatingController extends Controller
{
    use FormatsApplicantName;

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

        $isSecretariat = $user->canAccessAdminModule() || $this->isSecretariat($user->id);
        $composition = $this->getComposition($user->id);

        if (! $isSecretariat && ! $composition) {
            return response()->json(['message' => 'You are not an active HRMPSB member or HR-Chief.'], 403);
        }

        // Load applications that passed QS (status: qualified, exam_scheduled, etc.)
        $applications = Application::where('vacancy_id', $vacancy->id)
            ->with('anonymizationToken')
            ->with('applicant.user')
            ->whereNotIn('status', ['withdrawn', 'disqualified', 'submitted', 'under_review'])
            ->get()
            ->map(function ($app) use ($user, $isSecretariat) {
                $token = $app->anonymizationToken;
                $isUnmasked = $token?->isUnmasked();
                $profile = $app->applicant;

                $base = [
                    'id' => $app->id,
                    'token' => $token?->token ?? 'NO-TOKEN',
                    'unmasked' => $isUnmasked,
                    'name' => $isUnmasked ? $this->formatApplicantName($profile) : null,
                ];

                if ($isSecretariat) {
                    $base['all_ratings'] = CbweRating::where('application_id', $app->id)
                        ->with('evaluator:id,first_name,last_name,middle_name,suffix')
                        ->get()
                        ->map(fn ($r) => [
                            'evaluator' => $r->evaluator?->full_name,
                            'competency_scores' => $r->competency_scores,
                            'total_rating' => $r->total_rating,
                            'locked' => $r->isLocked(),
                        ]);
                } else {
                    $base['my_rating'] = CbweRating::where('application_id', $app->id)
                        ->where('evaluator_id', $user->id)
                        ->first();
                }

                return $base;
            });

        // Load vacancy competencies filtered to the 3 core CBWE competencies
        $vacancyCompetencies = VacancyCompetency::where('vacancy_id', $vacancy->id)
            ->whereIn('competency_key', CbweRating::COMPETENCY_KEYS)
            ->with('competency')
            ->get()
            ->sortBy(fn ($vc) => [$vc->competency?->competency_group, $vc->competency?->sort_order])
            ->mapWithKeys(fn ($vc) => [
                $vc->competency_key => [
                    'name' => $vc->competency?->competency_name ?? $vc->competency_key,
                    'group' => $vc->competency?->competency_group ?? 'Core',
                    'level' => $vc->competency_level,
                    'description' => $vc->competency?->description,
                ],
            ]);

        $allLocked = $applications->isNotEmpty()
            && $applications->every(fn ($app) => CbweRating::where('application_id', $app['id'])
                ->whereNotNull('locked_at')
                ->exists()
            );

        return response()->json([
            'vacancy' => $vacancy->only(
                'id', 'position_title', 'plantilla_no', 'salary_grade',
                'place_of_assignment', 'status', 'published_at'
            ),
            'competencies' => $vacancyCompetencies,
            'applications' => $applications,
            'is_secretariat' => $isSecretariat,
            'all_locked' => $allLocked,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'competency_scores' => 'required|array',
            'competency_scores.*' => 'required|integer|min:1|max:5',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $application = Application::with('vacancy')->findOrFail($data['application_id']);
        $user = $request->user();

        // Ensure submitted keys are among the 3 core CBWE competencies assigned to the vacancy
        $validKeys = VacancyCompetency::where('vacancy_id', $application->vacancy_id)
            ->whereIn('competency_key', CbweRating::COMPETENCY_KEYS)
            ->pluck('competency_key')
            ->toArray();

        $unknownKeys = array_diff(array_keys($data['competency_scores']), $validKeys);
        if (! empty($unknownKeys)) {
            return response()->json([
                'message' => 'One or more competency scores do not match the vacancy\'s CBWE competencies.',
            ], 422);
        }

        $composition = $this->getComposition($user->id);
        if (! $composition && ! $user->canAccessAdminModule()) {
            return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
        }

        $existing = CbweRating::where('application_id', $data['application_id'])
            ->where('evaluator_id', $user->id)
            ->first();

        if ($existing?->isLocked()) {
            return response()->json(['message' => 'CBWE ratings are locked and cannot be modified.'], 422);
        }

        $rating = CbweRating::updateOrCreate(
            ['application_id' => $data['application_id'], 'evaluator_id' => $user->id],
            [
                'competency_scores' => $data['competency_scores'],
                'remarks' => $data['remarks'] ?? null,
                'rated_at' => now(),
            ]
        );

        $rating->update(['total_rating' => $rating->computeTotalRating()]);

        AuditLog::record('cbwe_rating_submitted', $application);

        return response()->json($rating->fresh(), 201);
    }

    public function lock(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (! $this->isSecretariat($user->id) && ! $user->canAccessAdminModule()) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat or an admin-level user can lock CBWE ratings.'], 403);
        }

        $applicationIds = Application::where('vacancy_id', $vacancy->id)->pluck('id');

        $count = CbweRating::whereIn('application_id', $applicationIds)
            ->whereNull('locked_at')
            ->update(['locked_at' => now()]);

        Application::whereIn('id', $applicationIds)
            ->where('status', 'exam_scheduled')
            ->update(['status' => 'shortlisted', 'reviewed_at' => now()]);

        AuditLog::record('cbwe_ratings_locked', $vacancy);

        return response()->json([
            'message' => 'CBWE ratings locked successfully.',
            'locked_count' => $count,
        ]);
    }
}
