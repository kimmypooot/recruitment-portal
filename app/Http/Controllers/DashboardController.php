<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ExamSchedule;
use App\Models\InterviewSchedule;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $stats = Cache::remember('dashboard_stats', 3600, function () {
            return [
                'vacancies' => [
                    'total'        => Vacancy::count(),
                    'published'    => Vacancy::published()->count(),
                    'closing_soon' => Vacancy::published()->where('deadline_at', '<=', now()->addDays(7))->count(),
                ],
                'applications' => [
                    'total'      => Application::count(),
                    'pending'    => Application::where('status', 'submitted')->count(),
                    'this_month' => Application::whereMonth('created_at', now()->month)->count(),
                ],
                'pipeline' => Application::selectRaw('status, COUNT(*) as count')->groupBy('status')->get(),
            ];
        });

        return response()->json($stats);
    }

    public function recentApplications(): JsonResponse
    {
        $applications = Application::with([
            'vacancy:id,position_title,place_of_assignment',
            'applicant:id,user_id',
            'applicant.user:id,first_name,last_name,middle_name,suffix',
        ])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json($applications);
    }

    public function pipeline(): JsonResponse
    {
        $pipeline = Application::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return response()->json($pipeline);
    }

    public function upcoming(): JsonResponse
    {
        $exams = ExamSchedule::with([
            'application.vacancy:id,position_title,place_of_assignment',
            'application.applicant.user:id,first_name,last_name,middle_name,suffix',
        ])
        ->where('scheduled_at', '>=', now())
        ->orderBy('scheduled_at')
        ->limit(5)
        ->get();

        $interviews = InterviewSchedule::with([
            'application.vacancy:id,position_title,place_of_assignment',
            'application.applicant.user:id,first_name,last_name,middle_name,suffix',
        ])
        ->where('scheduled_at', '>=', now())
        ->orderBy('scheduled_at')
        ->limit(5)
        ->get();

        return response()->json([
            'exams'      => $exams,
            'interviews' => $interviews,
        ]);
    }

    public function adminStats(): JsonResponse
    {
        return $this->stats();
    }
}
