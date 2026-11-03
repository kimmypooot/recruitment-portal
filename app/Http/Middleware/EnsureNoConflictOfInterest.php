<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Application;
use App\Models\Vacancy;
use App\Services\AuditLog;
use App\Services\ConflictOfInterestService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `no-conflict` alias. Refuses an evaluator who has their own application on
 * the vacancy being acted on. Works out the vacancy from whatever the route or
 * body carries: a vacancy, an application, a record that belongs to one, or
 * application ids in the request body.
 */
class EnsureNoConflictOfInterest
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $service = app(ConflictOfInterestService::class);

            foreach ($this->vacancyIds($request) as $vacancyId) {
                if ($service->hasConflict($user, $vacancyId)) {
                    AuditLog::record('conflict_of_interest_blocked', $user);

                    return response()->json([
                        'message' => 'You have applied for this vacancy, so you cannot take part in evaluating it.',
                    ], 403);
                }
            }
        }

        return $next($request);
    }

    /** @return array<int, int> */
    private function vacancyIds(Request $request): array
    {
        $vacancies = [];
        $applications = [];

        foreach ($request->route()?->parameters() ?? [] as $name => $value) {
            if ($value instanceof Vacancy) {
                $vacancies[] = $value->id;
            } elseif ($value instanceof Application) {
                $vacancies[] = $value->vacancy_id;
            } elseif ($value instanceof Model) {
                $value->vacancy_id !== null && $vacancies[] = $value->vacancy_id;
                $value->application_id !== null && $applications[] = $value->application_id;
            } elseif (is_numeric($value)) {
                // Unbound id (e.g. {vacancy} on a controller that takes an int).
                if ($name === 'vacancy') {
                    $vacancies[] = (int) $value;
                } elseif ($name === 'application') {
                    $applications[] = (int) $value;
                }
            }
        }

        $vacancies = array_merge(
            $vacancies,
            array_filter([$request->input('vacancy_id') ? (int) $request->input('vacancy_id') : null]),
        );

        $applications = array_merge(
            $applications,
            array_filter([$request->input('application_id') ? (int) $request->input('application_id') : null]),
            array_map('intval', (array) $request->input('application_ids', [])),
        );

        if ($applications !== []) {
            $vacancies = array_merge(
                $vacancies,
                Application::withTrashed()->whereIn('id', $applications)->pluck('vacancy_id')->all(),
            );
        }

        return array_values(array_unique(array_map('intval', $vacancies)));
    }
}
