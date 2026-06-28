<?php

namespace App\Http\Controllers;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function generate(Request $request, string $type): JsonResponse
    {
        return match ($type) {
            'qualified-list'         => $this->qualifiedList($request),
            'comparative-assessment' => $this->comparativeAssessment($request),
            'appointment-report'     => $this->appointmentReport($request),
            'pipeline-summary'       => $this->pipelineSummary($request),
            'demographics-age'       => $this->demographicsAge($request),
            'demographics-gender'    => $this->demographicsGender($request),
            'demographics-civil-status' => $this->demographicsCivilStatus($request),
            'demographics-region'    => $this->demographicsRegion($request),
            'demographics-special'   => $this->demographicsSpecial($request),
            default                  => response()->json(['message' => "Unknown report type: {$type}."], 422),
        };
    }

    private function qualifiedList(Request $request): JsonResponse
    {
        $vacancyId = $request->query('vacancy_id');

        $query = Application::with(['applicant:id,user_id,eligibility', 'applicant.user:id,first_name,last_name', 'vacancy:id,position_title'])
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
                'applicant:id,user_id,eligibility',
                'applicant.user:id,first_name,last_name',
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
        $rows = Application::with(['applicant:id,user_id', 'applicant.user:id,first_name,last_name', 'vacancy:id,position_title,salary_grade'])
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
    // ── Demographic Reports ─────────────────────────────────────────────────

    private function baseProfileQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = ApplicantProfile::query();

        $vacancyId = $request->query('vacancy_id');
        if ($vacancyId) {
            $query->whereHas('applications', fn ($q) => $q->where('vacancy_id', $vacancyId));
        }

        return $query;
    }

    private function demographicsAge(Request $request): JsonResponse
    {
        $query = $this->baseProfileQuery($request);
        $profiles = $query->select('birthday')->whereNotNull('birthday')->get();

        $groups = ['18-25' => 0, '26-35' => 0, '36-45' => 0, '46-55' => 0, '56+' => 0, 'Unknown' => 0];
        foreach ($profiles as $p) {
            $age = $p->birthday?->age;
            if (!$age || $age < 18) { $groups['Unknown']++; continue; }
            if ($age <= 25) $groups['18-25']++;
            elseif ($age <= 35) $groups['26-35']++;
            elseif ($age <= 45) $groups['36-45']++;
            elseif ($age <= 55) $groups['46-55']++;
            else $groups['56+']++;
        }

        $total = array_sum($groups);
        $result = collect($groups)->map(fn ($count, $label) => [
            'label'      => $label,
            'count'      => $count,
            'percentage' => $total ? round($count / $total * 100, 1) : 0,
        ])->values();

        return response()->json(['report' => 'demographics-age', 'rows' => $result, 'total' => $total]);
    }

    private function demographicsGender(Request $request): JsonResponse
    {
        $query = $this->baseProfileQuery($request);
        $rows = $query->select('gender')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->orderByDesc('count')
            ->get();

        $total = $rows->sum('count');
        $result = $rows->map(fn ($r) => [
            'label'      => $r->gender,
            'count'      => (int) $r->count,
            'percentage' => $total ? round($r->count / $total * 100, 1) : 0,
        ]);

        return response()->json(['report' => 'demographics-gender', 'rows' => $result, 'total' => $total]);
    }

    private function demographicsCivilStatus(Request $request): JsonResponse
    {
        $query = $this->baseProfileQuery($request);
        $rows = $query->select('civil_status')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('civil_status')
            ->groupBy('civil_status')
            ->orderByDesc('count')
            ->get();

        $total = $rows->sum('count');
        $result = $rows->map(fn ($r) => [
            'label'      => $r->civil_status,
            'count'      => (int) $r->count,
            'percentage' => $total ? round($r->count / $total * 100, 1) : 0,
        ]);

        return response()->json(['report' => 'demographics-civil-status', 'rows' => $result, 'total' => $total]);
    }

    private function demographicsRegion(Request $request): JsonResponse
    {
        $query = $this->baseProfileQuery($request);
        $rows = $query->select('region')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('region')
            ->groupBy('region')
            ->orderByDesc('count')
            ->get();

        $total = $rows->sum('count');
        $result = $rows->map(fn ($r) => [
            'label'      => $r->region,
            'count'      => (int) $r->count,
            'percentage' => $total ? round($r->count / $total * 100, 1) : 0,
        ]);

        return response()->json(['report' => 'demographics-region', 'rows' => $result, 'total' => $total]);
    }

    private function demographicsSpecial(Request $request): JsonResponse
    {
        $query = $this->baseProfileQuery($request);
        $total = $query->count();

        $categories = [];
        foreach (['indigenous_group', 'pwd', 'solo_parent'] as $field) {
            $count = (clone $query)->where($field, 'Yes')->count();
            $label = match ($field) {
                'indigenous_group' => 'Indigenous Peoples',
                'pwd'              => 'Persons with Disability',
                'solo_parent'      => 'Solo Parent',
                default            => $field,
            };
            $categories[] = [
                'label'      => $label,
                'field'      => $field,
                'count'      => $count,
                'percentage' => $total ? round($count / $total * 100, 1) : 0,
            ];
        }

        $categories[] = [
            'label'      => 'None of the Above',
            'field'      => 'none',
            'count'      => max(0, $total - collect($categories)->sum('count')),
            'percentage' => $total ? round(max(0, $total - collect($categories)->sum('count')) / $total * 100, 1) : 0,
        ];

        return response()->json(['report' => 'demographics-special', 'rows' => $categories, 'total' => $total]);
    }
}
