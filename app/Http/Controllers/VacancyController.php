<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVacancyRequest;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $query = $this->baseQuery($request);

        // Status filter — supports single value or comma-separated list (e.g. "published,closed")
        if ($request->filled('status')) {
            $statuses = is_array($request->status)
                ? $request->status
                : array_filter(explode(',', $request->status));
            $query->whereIn('status', $statuses);
        }

        // Sorting
        match ($request->sort) {
            'deadline_desc' => $query->orderBy('deadline_at', 'desc'),
            'sg_desc' => $query->orderBy('salary_grade', 'desc'),
            'sg_asc' => $query->orderBy('salary_grade', 'asc'),
            'newest' => $query->orderBy('published_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $perPage = min((int) ($request->per_page ?? 15), 100);

        $vacancies = $query
            ->with('postedBy:id,first_name,last_name,middle_name,suffix')
            ->withCount('applications')
            ->paginate($perPage)
            ->withQueryString();

        return VacancyResource::collection($vacancies)
            ->response();
    }

    /**
     * Vacancy counts per status, for the status-tab UI.
     * Respects the same search/salary_grade/place filters as index(),
     * but ignores the status filter itself so every tab's count is visible.
     */
    public function statusCounts(Request $request): JsonResponse
    {
        $counts = $this->baseQuery($request)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'all' => $counts->sum(),
            ...$counts,
        ]);
    }

    /**
     * Base query shared by index() and statusCounts(): search, salary grade,
     * place-of-assignment filters, plus the public/authenticated visibility rule.
     */
    private function baseQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Vacancy::query();

        // This route has no auth:sanctum middleware (it must stay open to
        // public browsing), so the default 'web' session guard checked by
        // plain auth()->check() is never true for the SPA's Bearer-token
        // requests. Resolve the 'sanctum' guard explicitly.
        // Only admin-module users may see unpublished/expired vacancies —
        // an authenticated applicant is still a member of the public.
        if (! auth('sanctum')->user()?->canAccessAdminModule()) {
            $query->where('status', 'published')
                ->where('deadline_at', '>=', now());
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('position_title', 'like', "%{$request->search}%")
                    ->orWhere('place_of_assignment', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('salary_grade')) {
            $query->where('salary_grade', $request->salary_grade);
        }

        if ($request->filled('place')) {
            $query->where('place_of_assignment', $request->place);
        }

        return $query;
    }

    /**
     * Show single vacancy
     */
    public function show(Vacancy $vacancy): JsonResponse
    {
        // Unpublished vacancies are pre-decisional — only admin-module users
        // may view them. 404 (not 403) to avoid confirming the ID exists.
        if ($vacancy->status !== 'published' && ! auth('sanctum')->user()?->canAccessAdminModule()) {
            abort(404);
        }

        return (new VacancyResource($vacancy->load('postedBy', 'competencies')))
            ->response();
    }

    /**
     * Update vacancy
     */
    public function update(Request $request, Vacancy $vacancy): JsonResponse
    {
        $this->authorize('update', $vacancy);

        $data = $request->validate([
            'position_title' => 'sometimes|required|string|max:255',
            'plantilla_no' => 'sometimes|required|string|max:100',
            'salary_grade' => 'sometimes|required|integer|between:1,33',
            'monthly_salary' => 'nullable|numeric|min:0',
            'position_level' => 'nullable|string|max:100',
            'is_anticipated_vacancy' => 'boolean',
            'place_of_assignment' => ['sometimes', 'required', 'string', Rule::in(Vacancy::placesOfAssignment())],
            'education_req' => 'sometimes|required|string',
            'experience_req' => 'sometimes|required|string',
            'training_req' => 'sometimes|required|string',
            'eligibility_req' => 'sometimes|required|string',
            'deadline_at' => 'nullable|date',
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
        $deadline = $publishedAt->copy()->addDays(10);

        $vacancy->update([
            'status' => 'published',
            'published_at' => $publishedAt,
            'deadline_at' => $deadline,
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

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:vacancies,id',
            'status' => 'required|in:published,closed,draft,archived,filled',
        ]);

        $count = Vacancy::whereIn('id', $request->ids)->update([
            'status' => $request->status,
        ]);

        AuditLog::record("bulk_vacancy_status_update:{$request->status}", new Vacancy);

        return response()->json([
            'success' => true,
            'message' => "{$count} vacancy(ies) updated to '{$request->status}'.",
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
