<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreVacancyRequest;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use App\Events\VacancyStateChanged;
use Illuminate\Http\JsonResponse;
use App\Services\ImisPlantilla;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

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

        // A Contract of Service position has no item number or salary grade.
        $plantilla = $vacancy->employment_type !== 'contract_of_service';

        $data = $request->validate([
            'position_title' => 'sometimes|required|string|max:255',
            'plantilla_no' => [$plantilla ? 'sometimes' : 'nullable', $plantilla ? 'required' : 'nullable', 'string', 'max:100'],
            'salary_grade' => [$plantilla ? 'sometimes' : 'nullable', $plantilla ? 'required' : 'nullable', 'integer', 'between:1,33'],
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

        // IMIS owns these on a vacancy opened from its plantilla item.
        if ($vacancy->isLinkedToImis()) {
            $data = Arr::except($data, Vacancy::IMIS_OWNED);
        }

        $vacancy->update($data);

        VacancyStateChanged::dispatch($vacancy, 'updated');

        return (new VacancyResource($vacancy->fresh()))
            ->response();
    }

    /**
     * Create vacancy
     */
    public function store(StoreVacancyRequest $request, ImisPlantilla $imis): JsonResponse
    {
        $this->authorize('create', Vacancy::class);

        $data = $request->validated();

        if (! empty($data['imis_plantilla_item_id'])) {
            // The client's copy of the IMIS fields is never trusted: they are re-read
            // from IMIS, which also confirms the item is still vacant.
            try {
                $item = collect($imis->vacantPositions())->firstWhere('id', (int) $data['imis_plantilla_item_id']);
            } catch (Throwable $e) {
                Log::warning('IMIS vacant positions failed', ['message' => $e->getMessage()]);

                return response()->json(['message' => 'IMIS could not be reached. Try again, or enter the vacancy by hand.'], 502);
            }

            if ($item === null || $item['place_of_assignment'] === null) {
                return response()->json(['message' => 'That position is no longer vacant in IMIS, or its office is not one of the recruiting offices.'], 422);
            }

            $data = [
                ...$data,
                'employment_type' => 'plantilla',
                'position_title' => $item['position_title'],
                'plantilla_no' => $item['item_number'],
                'salary_grade' => $item['salary_grade'],
                'monthly_salary' => $item['monthly_salary'],
                'place_of_assignment' => $item['place_of_assignment'],
            ];
        }

        $vacancy = Vacancy::create([
            ...$data,
            'posted_by' => auth()->id(),
            'status' => 'draft',
        ]);

        VacancyStateChanged::dispatch($vacancy, 'created');

        return (new VacancyResource($vacancy))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Vacant IMIS plantilla items a vacancy can be opened on, minus those that
     * already have a live vacancy here.
     */
    public function imisPositions(ImisPlantilla $imis): JsonResponse
    {
        $this->authorize('create', Vacancy::class);

        if (! $imis->enabled()) {
            return response()->json(['enabled' => false, 'data' => []]);
        }

        try {
            $positions = $imis->vacantPositions();
        } catch (Throwable $e) {
            Log::warning('IMIS vacant positions failed', ['message' => $e->getMessage()]);

            return response()->json(['message' => 'IMIS could not be reached. You can still enter the vacancy by hand.'], 502);
        }

        $taken = Vacancy::whereNotNull('imis_plantilla_item_id')->pluck('imis_plantilla_item_id')->all();

        return response()->json([
            'enabled' => true,
            'data' => array_values(array_filter($positions, fn ($p) => $p['place_of_assignment'] !== null && ! in_array($p['id'], $taken, true))),
        ]);
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

        VacancyStateChanged::dispatch($vacancy, 'published');

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

        VacancyStateChanged::dispatch($vacancy, 'archived');

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

        VacancyStateChanged::dispatch(new Vacancy, "bulk_vacancy_status_update:{$request->status}");


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

        VacancyStateChanged::dispatch($vacancy, 'deleted');

        $vacancy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vacancy deleted successfully.',
        ]);
    }
}
