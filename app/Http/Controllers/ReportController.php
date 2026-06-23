<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\SubmissionTracking;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function generate(Request $request, string $type): JsonResponse
    {
        return match ($type) {
            'qualified-list'         => $this->qualifiedList($request),
            'comparative-assessment' => $this->comparativeAssessment($request),
            'appointment-report'     => $this->appointmentReport($request),
            'pipeline-summary'       => $this->pipelineSummary($request),
            'compliance-deadlines'   => $this->complianceDeadlines($request),
            default                  => response()->json(['message' => "Unknown report type: {$type}."], 422),
        };
    }

    private function qualifiedList(Request $request): JsonResponse
    {
        $vacancyId = $request->query('vacancy_id');

        $query = Application::with(['applicant:id,first_name,last_name,eligibility', 'vacancy:id,position_title'])
            ->whereIn('status', ['qualified', 'exam_scheduled', 'interviewed', 'shortlisted', 'recommended', 'appointed']);

        if ($vacancyId) {
            $query->where('vacancy_id', $vacancyId);
        }

        $rows = $query->get()->map(fn ($app) => [
            'application_id' => $app->id,
            'applicant'      => trim(($app->applicant->first_name ?? '') . ' ' . ($app->applicant->last_name ?? '')),
            'eligibility'    => $app->applicant->eligibility ?? '—',
            'position'       => $app->vacancy->position_title ?? '—',
            'status'         => $app->status,
        ]);

        return response()->json(['report' => 'qualified-list', 'rows' => $rows, 'count' => $rows->count()]);
    }

    private function comparativeAssessment(Request $request): JsonResponse
    {
        $vacancyId = $request->query('vacancy_id');

        if (!$vacancyId) {
            return response()->json(['message' => 'vacancy_id is required for comparative assessment.'], 422);
        }

        $applications = Application::where('vacancy_id', $vacancyId)
            ->with([
                'applicant:id,first_name,last_name,eligibility',
                'qsEvaluations',
                'examResults',
                'beiRatings',
                'anonymizationToken',
            ])
            ->whereNotIn('status', ['withdrawn'])
            ->get()
            ->map(function ($app) {
                $qsQualified = $app->qsEvaluations->where('overall_qualified', true)->count();
                $qsTotal     = $app->qsEvaluations->count();

                $tweScore  = $app->examResults->firstWhere('exam_type', 'TWE');
                $cbweScore = $app->examResults->firstWhere('exam_type', 'CBWE');
                $beiAvg    = $app->beiRatings->whereNotNull('total_rating')->avg('total_rating');

                $isUnmasked = $app->anonymizationToken?->isUnmasked();

                return [
                    'token'       => $app->anonymizationToken?->token ?? "APP-{$app->id}",
                    'name'        => $isUnmasked ? trim(($app->applicant->first_name ?? '') . ' ' . ($app->applicant->last_name ?? '')) : null,
                    'eligibility' => $app->applicant->eligibility ?? '—',
                    'status'      => $app->status,
                    'qs'          => $qsTotal ? ($qsQualified > $qsTotal / 2 ? 'Qualified' : 'Disqualified') : '—',
                    'twe'         => $tweScore ? $tweScore->raw_score . '/' . $tweScore->max_score : '—',
                    'cbwe'        => $cbweScore ? $cbweScore->raw_score . '/' . $cbweScore->max_score : '—',
                    'bei_avg'     => $beiAvg ? round($beiAvg, 2) : '—',
                ];
            });

        return response()->json(['report' => 'comparative-assessment', 'rows' => $applications]);
    }

    private function appointmentReport(Request $request): JsonResponse
    {
        $rows = Application::with(['applicant:id,first_name,last_name', 'vacancy:id,position_title,salary_grade'])
            ->where('status', 'appointed')
            ->get()
            ->map(fn ($app) => [
                'application_id' => $app->id,
                'appointee'      => trim(($app->applicant->first_name ?? '') . ' ' . ($app->applicant->last_name ?? '')),
                'position'       => $app->vacancy->position_title ?? '—',
                'salary_grade'   => $app->vacancy->salary_grade ?? '—',
                'appointed_at'   => $app->updated_at?->format('Y-m-d'),
            ]);

        return response()->json(['report' => 'appointment-report', 'rows' => $rows, 'count' => $rows->count()]);
    }

    private function pipelineSummary(Request $request): JsonResponse
    {
        $pipeline = DB::table('applications')
            ->select('status', DB::raw('count(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        $vacancies = DB::table('vacancies')
            ->select('status', DB::raw('count(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->get();

        return response()->json([
            'report'     => 'pipeline-summary',
            'pipeline'   => $pipeline,
            'vacancies'  => $vacancies,
            'generated'  => now()->toDateTimeString(),
        ]);
    }

    private function complianceDeadlines(Request $request): JsonResponse
    {
        $rows = SubmissionTracking::with([
            'application.applicant:id,first_name,last_name',
            'vacancy:id,position_title',
        ])
            ->orderBy('due_at')
            ->get()
            ->map(fn ($t) => [
                'application_id' => $t->application_id,
                'appointee'      => trim(($t->application->applicant->first_name ?? '') . ' ' . ($t->application->applicant->last_name ?? '')),
                'position'       => $t->vacancy->position_title ?? '—',
                'due_at'         => $t->due_at?->format('Y-m-d'),
                'days_remaining' => $t->daysRemaining(),
                'status'         => $t->isOverdue() ? 'overdue' : $t->status,
                'submitted_at'   => $t->submitted_at?->format('Y-m-d'),
            ]);

        return response()->json(['report' => 'compliance-deadlines', 'rows' => $rows]);
    }
}
