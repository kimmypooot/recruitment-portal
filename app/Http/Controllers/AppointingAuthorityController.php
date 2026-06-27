<?php

namespace App\Http\Controllers;

use App\Models\AppointingAuthorityDecision;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\AuditLog;
use App\Traits\FormatsApplicantName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointingAuthorityController extends Controller
{
    use FormatsApplicantName;
    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $applications = Application::with([
            'applicant.user',
            'examResults',
            'cbweRatings',
            'beiRatings',
            'qsEvaluations',
            'backgroundInvestigationReports',
            'eoptResult',
            'deliberationResults' => function ($q) use ($vacancy) {
                $q->where('vacancy_id', $vacancy->id)->where('action', 'endorsed');
            },
        ])->where('vacancy_id', $vacancy->id)
          ->whereHas('deliberationResults', function ($q) use ($vacancy) {
              $q->where('vacancy_id', $vacancy->id)->where('action', 'endorsed');
          })->get()
          ->map(function ($app) {
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

              return [
                  'id'          => $app->id,
                  'token'       => $app->token,
                  'name'        => $this->formatApplicantName($app->applicant) ?: '—',
                  'qs_result'   => $qsResult,
                  'exam_scores' => $exams,
                  'cbwe_average' => $app->cbweRatings->whereNotNull('total_rating')->count() > 0
                      ? round($app->cbweRatings->avg('total_rating'), 2)
                      : null,
                  'bei_average' => $beiAverage,
                  'background_investigation' => $app->backgroundInvestigationReports->first() ? [
                      'submitted'       => (bool) $app->backgroundInvestigationReports->first()?->submitted_at,
                      'investigator'    => $app->backgroundInvestigationReports->first()?->investigator_name,
                      'on_competencies' => $app->backgroundInvestigationReports->first()?->on_competencies,
                      'on_performance'  => $app->backgroundInvestigationReports->first()?->on_performance,
                  ] : null,
                  'eopt' => $app->eoptResult?->only([
                      'emotional_stability', 'extraversion', 'openness_to_experience',
                      'agreeableness', 'conscientiousness',
                  ]) ?: null,
                  'deliberation' => [
                      'action'  => $app->deliberationResults->first()?->action,
                      'rank'    => $app->deliberationResults->first()?->rank,
                      'remarks' => $app->deliberationResults->first()?->remarks,
                  ],
              ];
          });

        $decisions = AppointingAuthorityDecision::where('vacancy_id', $vacancy->id)
            ->get()
            ->keyBy('application_id');

        $canDecide = $user->canAccessAdminModule()
            || \App\Models\HrmbsboardComposition::where('user_id', $user->id)
                ->whereIn('hrmpsb_role', ['chairperson', 'secretariat', 'appointing-authority'])
                ->where('is_active', true)
                ->exists();

        return response()->json([
            'eopt_definitions' => \App\Models\EoptResult::getAllDefinitions(),
            'vacancy' => $vacancy->only(
                'id', 'position_title', 'plantilla_no', 'salary_grade',
                'place_of_assignment', 'status', 'published_at', 'deadline_at'
            ),
            'applications' => $applications->map(function ($app) use ($decisions) {
                $decision = $decisions->get($app['id']);
                $app['aa_decision'] = $decision ? [
                    'action'     => $decision->action,
                    'decided_at' => $decision->decided_at,
                    'decided_by' => $decision->decidedBy?->full_name,
                    'remarks'    => $decision->remarks,
                ] : null;
                return $app;
            })->values(),
            'can_decide' => $canDecide,
        ]);
    }

    public function decide(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $canDecide = $user->canAccessAdminModule()
            || \App\Models\HrmbsboardComposition::where('user_id', $user->id)
                ->whereIn('hrmpsb_role', ['chairperson', 'secretariat', 'appointing-authority'])
                ->where('is_active', true)
                ->exists();

        if (! $canDecide) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'action'         => 'required|in:appointed,not_appointed',
            'remarks'        => 'nullable|string|max:1000',
        ]);

        $application = Application::findOrFail($data['application_id']);

        $decision = AppointingAuthorityDecision::updateOrCreate(
            [
                'vacancy_id'     => $vacancy->id,
                'application_id' => $data['application_id'],
            ],
            [
                'action'     => $data['action'],
                'decided_by' => $user->id,
                'decided_at' => now(),
                'remarks'    => $data['remarks'] ?? null,
            ]
        );

        if ($data['action'] === 'appointed') {
            $application->update(['status' => 'appointed']);
        }

        AuditLog::record('appointing_authority_decision', $application);

        return response()->json([
            'message' => 'Decision recorded successfully.',
        ]);
    }
}
