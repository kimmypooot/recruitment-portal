<?php

namespace App\Http\Controllers;

use App\Models\Application;
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
        $applications = Application::with(['vacancy', 'applicant'])
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

    public function adminStats(): JsonResponse
    {
        return $this->stats();
    }
}
