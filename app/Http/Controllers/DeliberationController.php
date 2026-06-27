<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\DeliberationResult;
use App\Models\EoptResult;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Services\AnonymizationService;
use App\Services\AuditLog;
use App\Traits\FormatsApplicantName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliberationController extends Controller
{
    use FormatsApplicantName;

    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $isMember = $user->canAccessAdminModule()
            || HrmbsboardComposition::where('user_id', $user->id)
                ->where('is_active', true)
                ->exists();

        if (! $isMember) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->with(['anonymizationToken', 'applicant', 'examResults', 'beiRatings', 'qsEvaluations', 'backgroundInvestigationReports', 'eoptResult', 'cbweRatings'])
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
                    'raw_score' => $r->raw_score,
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

                // EOPT result
                $eopt = $app->eoptResult;

                // Background Investigation: latest submitted report
                $biReport = $app->backgroundInvestigationReports
                    ->whereNotNull('submitted_at')
                    ->sortByDesc('submitted_at')
                    ->first();

                return [
                    'id' => $app->id,
                    'token' => $token?->token,
                    'unmasked' => $isUnmasked,
                    'name' => $isUnmasked ? $this->formatApplicantName($app->applicant) : null,
                    'status' => $app->status,
                    'qs_result' => $qsResult,
                    'exam_scores' => $exams,
                    'cbwe_average' => $app->cbweRatings->whereNotNull('total_rating')->count() > 0
                        ? round($app->cbweRatings->avg('total_rating'), 2)
                        : null,
                    'bei_average' => $beiAverage,
                    'eopt' => $eopt ? [
                        'emotional_stability' => $eopt->emotional_stability,
                        'extraversion' => $eopt->extraversion,
                        'openness_to_experience' => $eopt->openness_to_experience,
                        'agreeableness' => $eopt->agreeableness,
                        'conscientiousness' => $eopt->conscientiousness,
                        'remarks' => $eopt->remarks,
                    ] : null,
                    'background_investigation' => $biReport ? [
                        'submitted' => true,
                        'submitted_at' => $biReport->submitted_at->toISOString(),
                        'investigator' => $biReport->investigator_name,
                        'on_competencies' => $biReport->on_competencies,
                        'on_performance' => $biReport->on_performance,
                    ] : ['submitted' => false],
                    'deliberation' => $deliberation ? [
                        'action' => $deliberation->action,
                        'rank' => $deliberation->rank,
                        'remarks' => $deliberation->remarks,
                    ] : null,
                ];
            });

        $locked = DeliberationResult::where('vacancy_id', $vacancy->id)
            ->whereNotNull('locked_at')
            ->exists();

        return response()->json([
            'vacancy' => $vacancy->only(
                'id', 'position_title', 'plantilla_no', 'salary_grade',
                'place_of_assignment', 'status', 'published_at'
            ),
            'applications' => $applications,
            'can_unmask' => $user->canAccessAdminModule(),
            'can_decide' => $user->canAccessAdminModule()
                || HrmbsboardComposition::where('user_id', $user->id)
                    ->whereIn('hrmpsb_role', ['chairperson'])
                    ->where('is_active', true)
                    ->exists(),
            'can_lock' => $user->canAccessAdminModule()
                || HrmbsboardComposition::where('user_id', $user->id)
                    ->whereIn('hrmpsb_role', ['chairperson', 'secretariat'])
                    ->where('is_active', true)
                    ->exists(),
            'locked' => $locked,
            'eopt_definitions' => EoptResult::getAllDefinitions(),
        ]);
    }

    public function unmask(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (! $user->canAccessAdminModule()) {
            return response()->json(['message' => 'Only admin-level users can unmask applicant identities.'], 403);
        }

        (new AnonymizationService)->unmaskVacancy($vacancy, $user);

        AuditLog::record('identities_unmasked', $vacancy);

        return response()->json(['message' => 'Applicant identities have been revealed for deliberation.']);
    }

    public function decide(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $isChairOrAbove = $user->canAccessAdminModule()
            || HrmbsboardComposition::where('user_id', $user->id)
                ->whereIn('hrmpsb_role', ['chairperson'])
                ->where('is_active', true)
                ->exists();

        if (! $isChairOrAbove) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'action' => 'required|in:endorsed,appointed,not_recommended',
            'rank' => 'nullable|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $locked = DeliberationResult::where('vacancy_id', $vacancy->id)
            ->whereNotNull('locked_at')
            ->exists();

        if ($locked) {
            return response()->json(['message' => 'Deliberation results are locked and cannot be modified.'], 422);
        }

        $application = Application::findOrFail($data['application_id']);

        $result = DeliberationResult::updateOrCreate(
            [
                'vacancy_id' => $vacancy->id,
                'application_id' => $data['application_id'],
            ],
            [
                'action' => $data['action'],
                'rank' => $data['rank'] ?? null,
                'decided_by' => $user->id,
                'decided_at' => now(),
                'remarks' => $data['remarks'] ?? null,
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

    public function lock(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $canLock = $user->canAccessAdminModule()
            || HrmbsboardComposition::where('user_id', $user->id)
                ->whereIn('hrmpsb_role', ['chairperson', 'secretariat'])
                ->where('is_active', true)
                ->exists();

        if (! $canLock) {
            return response()->json(['message' => 'Only the Chairperson, Secretariat, or an admin-level user can lock deliberation results.'], 403);
        }

        $count = DeliberationResult::where('vacancy_id', $vacancy->id)
            ->whereNull('locked_at')
            ->update(['locked_at' => now()]);

        AuditLog::record('deliberation_locked', $vacancy);

        return response()->json([
            'message'      => 'Deliberation results locked successfully.',
            'locked_count' => $count,
        ]);
    }
}
