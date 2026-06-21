<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\VacancyResource;
use App\Http\Requests\StoreVacancyRequest;
use App\Services\AuditLog;

class VacancyController extends Controller
{
    /**
     * List vacancies
     *
     * Public:
     * GET /api/vacancies
     *
     * HR:
     * GET /api/admin/vacancies
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vacancy::query();

        // Public users only see published vacancies
        if (!auth()->check()) {
            $query->where('status', 'published')
                  ->where('deadline_at', '>=', now());
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('position_title', 'like', "%{$request->search}%")
                  ->orWhere('place_of_assignment', 'like', "%{$request->search}%");
            });
        }

        // Status filter (HR side)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Salary Grade filter
        if ($request->filled('salary_grade')) {
            $query->where('salary_grade', $request->salary_grade);
        }

        // Place filter
        if ($request->filled('place')) {
            $query->where('place_of_assignment', $request->place);
        }

        // Sorting
        match ($request->sort) {
            'deadline_desc' => $query->orderBy('deadline_at', 'desc'),
            'sg_desc'       => $query->orderBy('salary_grade', 'desc'),
            'sg_asc'        => $query->orderBy('salary_grade', 'asc'),
            'newest'        => $query->orderBy('published_at', 'desc'),
            default         => $query->orderBy('created_at', 'desc'),
        };

        $vacancies = $query
            ->with('postedBy:id,name')
            ->paginate(15)
            ->withQueryString();

        return VacancyResource::collection($vacancies)
            ->response();
    }

    /**
     * Show single vacancy
     */
    public function show(Vacancy $vacancy): JsonResponse
    {
        return (new VacancyResource($vacancy->load('postedBy')))
            ->response();
    }

    /**
     * Create vacancy
     */
    public function store(StoreVacancyRequest $request): JsonResponse
    {
        $this->authorize('create', Vacancy::class);

        $vacancy = Vacancy::create([
            ...$request->validated(),
            'posted_by' => auth()->id(),
            'status' => 'draft',
        ]);

        AuditLog::record('created', $vacancy);

        return (new VacancyResource($vacancy))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Publish vacancy
     */
    public function publish(Vacancy $vacancy): JsonResponse
    {
        $this->authorize('publish', $vacancy);

        $vacancy->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        AuditLog::record('published', $vacancy);

        return response()->json([
            'success' => true,
            'message' => 'Vacancy published successfully.',
        ]);
    }

    /**
     * Archive vacancy
     */
    public function archive(Vacancy $vacancy): JsonResponse
    {
        $this->authorize('archive', $vacancy);

        $vacancy->update([
            'status' => 'archived',
        ]);

        AuditLog::record('archived', $vacancy);

        return response()->json([
            'success' => true,
            'message' => 'Vacancy archived successfully.',
        ]);
    }

    /**
     * Delete vacancy
     */
    public function destroy(Vacancy $vacancy): JsonResponse
    {
        $this->authorize('delete', $vacancy);

        AuditLog::record('deleted', $vacancy);

        $vacancy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vacancy deleted successfully.',
        ]);
    }
}