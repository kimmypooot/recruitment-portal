<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\VacancyResource;
use App\Http\Requests\StoreVacancyRequest;
use App\Models\ComplianceDeadline;
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

        // Status filter — supports single value or comma-separated list (e.g. "published,closed")
        if ($request->filled('status')) {
            $statuses = is_array($request->status)
                ? $request->status
                : array_filter(explode(',', $request->status));
            $query->whereIn('status', $statuses);
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

        $perPage = min((int) ($request->per_page ?? 15), 100);

        $vacancies = $query
            ->with('postedBy:id,name')
            ->withCount('applications')
            ->paginate($perPage)
            ->withQueryString();

        return VacancyResource::collection($vacancies)
            ->response();
    }

    /**
     * Show single vacancy
     */
    public function show(Vacancy $vacancy): JsonResponse
    {
        return (new VacancyResource($vacancy->load('postedBy', 'competencies')))
            ->response();
    }

    /**
     * Update vacancy
     */
    public function update(Request $request, Vacancy $vacancy): JsonResponse
    {
        $data = $request->validate([
            'position_title'         => 'sometimes|required|string|max:255',
            'item_number'            => 'sometimes|required|string|max:100',
            'plantilla_number'       => 'nullable|string|max:100',
            'salary_grade'           => 'sometimes|required|integer|between:1,33',
            'monthly_salary'         => 'nullable|numeric|min:0',
            'position_level'         => 'nullable|string|max:100',
            'is_anticipated_vacancy' => 'boolean',
            'place_of_assignment'    => 'sometimes|required|string|max:255',
            'education_req'          => 'sometimes|required|string',
            'experience_req'         => 'sometimes|required|string',
            'training_req'           => 'sometimes|required|string',
            'eligibility_req'        => 'sometimes|required|string',
            'deadline_at'            => 'nullable|date',
        ]);

        $vacancy->update($data);

        AuditLog::record('updated', $vacancy);

        return (new VacancyResource($vacancy->fresh()))
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

        $publishedAt = now();
        $deadline    = $publishedAt->copy()->addDays(10);

        $vacancy->update([
            'status'       => 'published',
            'published_at' => $publishedAt,
            'deadline_at'  => $deadline, // enforce 10-calendar-day rule per CSC policy
        ]);

        ComplianceDeadline::updateOrCreate(
            ['vacancy_id' => $vacancy->id, 'deadline_type' => 'publication'],
            ['due_at' => $deadline, 'completed_at' => null]
        );

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