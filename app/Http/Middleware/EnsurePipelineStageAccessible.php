<?php

namespace App\Http\Middleware;

use App\Models\Vacancy;
use App\Services\PipelineStageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePipelineStageAccessible
{
    public function handle(Request $request, Closure $next, string $stageKey): Response
    {
        $vacancyId = $request->route('vacancy');

        if (!$vacancyId) {
            return $next($request);
        }

        $vacancy = Vacancy::find($vacancyId);

        if (!$vacancy) {
            return $next($request);
        }

        $service = app(PipelineStageService::class);

        if (!$service->isStageAccessible($stageKey, $vacancy)) {
            return redirect()->to('/hrmpsb/dashboard')
                ->with('error', 'The requested stage is not yet available. Complete the prerequisite stages first.');
        }

        return $next($request);
    }
}
