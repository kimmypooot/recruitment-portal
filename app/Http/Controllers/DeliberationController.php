<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BeiRating;
use App\Models\DeliberationResult;
use App\Models\ExamResult;
use App\Models\HrmbsboardComposition;
use App\Models\QsEvaluation;
use App\Models\Vacancy;
use App\Services\AnonymizationService;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliberationController extends Controller
{
    use \App\Traits\FormatsApplicantName;
    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $isChairOrAbove = in_array($user->role, ['admin', 'hr-manager', 'appointing-authority'])
            || HrmbsboardComposition::where('user_id', $user->id)
                ->whereIn('hrmpsb_role', ['chairperson', 'secretariat'])
                ->where('is_active', true)
                ->exists();

        if (!$isChairOrAbove) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->with(['anonymizationToken', 'applicant', 'examResults', 'beiRatings', 'qsEvaluations'])
            ->get()
            ->map(function ($app) {
                $token = $app->anonymizationToken;
                $isUnmasked = $token?->isUnmasked();

                // QS: qualified/disqualified majority vote
                $qsQualifiedVotes = $app->qsEvaluations->where('overall_qualified', true)->count();
                $qsTotal = $app->qsEvaluations->count();
                $qsResult = $qsTotal > 0 ? ($qsQualifiedVotes > ($qsTotal / 2) ? 'qualified' : 'disqualified') : null;

                // Exam scores
                $exams = $app->examResults->keyBy('exam_type')->map(fn ($r) => [
                    'raw_score'  => $r->raw_score,
                    'percentage' => $r->percentage,
                ]);

                // BEI: average of all evaluator totals
                $beiRatings = $app->beiRatings->whereNotNull('total_rating');
                $beiAverage = $beiRatings->count() > 0
                    ? round($beiRatings->avg('total_rating'), 2)
                    : null;

                $deliberation = DeliberationResult::where('application_id', $app->id)
                    ->where('vacancy_id', $app->vacancy_id)
                    ->first();

                return [
                    'id'          => $app->id,
                    'token'       => $token?->token,
                    'unmasked'    => $isUnmasked,
                    'name'        => $isUnmasked ? $this->formatApplicantName($app->applicant) : null,
                    'status'      => $app->status,
                    'qs_result'   => $qsResult,
                    'exam_scores' => $exams,
                    'bei_average' => $beiAverage,
                    'deliberation' => $deliberation ? [
                        'action'  => $deliberation->action,
                        'rank'    => $deliberation->rank,
                        'remarks' => $deliberation->remarks,
                    ] : null,
                ];
            });

        return response()->json([
            'vacancy'      => $vacancy->only(
                'id', 'position_title', 'item_number', 'salary_grade',
                'place_of_assignment', 'status', 'published_at'
            ),
            'applications' => $applications,
            'can_unmask'   => in_array($user->role, ['admin', 'hr-manager', 'appointing-authority']),
        ]);
    }

    public function unmask(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'hr-manager', 'appointing-authority'])) {
            return response()->json(['message' => 'Only authorized roles can unmask applicant identities.'], 403);
        }

        (new AnonymizationService())->unmaskVacancy($vacancy, $user);

        AuditLog::record('identities_unmasked', $vacancy);

        return response()->json(['message' => 'Applicant identities have been revealed for deliberation.']);
    }

    public function decide(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $isChairOrAbove = in_array($user->role, ['admin', 'hr-manager', 'appointing-authority'])
            || HrmbsboardComposition::where('user_id', $user->id)
                ->whereIn('hrmpsb_role', ['chairperson'])
                ->where('is_active', true)
                ->exists();

        if (!$isChairOrAbove) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'action'         => 'required|in:endorsed,appointed,not_recommended',
            'rank'           => 'nullable|integer|min:1',
            'remarks'        => 'nullable|string|max:1000',
        ]);

        $application = Application::findOrFail($data['application_id']);

        $result = DeliberationResult::updateOrCreate(
            [
                'vacancy_id'     => $vacancy->id,
                'application_id' => $data['application_id'],
                'action'         => $data['action'],
            ],
            [
                'rank'       => $data['rank'] ?? null,
                'decided_by' => $user->id,
                'decided_at' => now(),
                'remarks'    => $data['remarks'] ?? null,
            ]
        );

        // Update application status if appointed
        if ($data['action'] === 'appointed') {
            $application->update(['status' => 'appointed']);
        } elseif ($data['action'] === 'endorsed') {
            $application->update(['status' => 'recommended']);
        }

        AuditLog::record('deliberation_decision', $application);

        return response()->json($result, 201);
    }
}
