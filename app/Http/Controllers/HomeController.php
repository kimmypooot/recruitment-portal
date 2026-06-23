<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Vacancy;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        // Initial vacancies loaded server-side so the page has content
        // immediately (no loading flash on first visit)
        $vacancies = Vacancy::query()
            ->where('status', 'published')
            ->where('deadline_at', '>=', now())
            ->orderBy('deadline_at', 'asc')
            ->paginate(9);   // 9 cards = 3×3 grid

        // Quick stats for the hero badge and info strip
        $stats = [
            'published'          => Vacancy::where('status', 'published')->count(),
            'total_applications' => Application::whereYear('created_at', now()->year)->count(),
            'appointed'          => Application::where('status', 'appointed')->count(),
        ];

        return Inertia::render('Home', [
            'initialVacancies' => $vacancies,
            'stats'            => $stats,
        ]);
    }
}