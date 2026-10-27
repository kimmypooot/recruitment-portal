<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Application;
use App\Models\Vacancy;
use App\Services\ImisPlantilla;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Reports the Appointing Authority's selection to IMIS. Queued so a slow or
 * unreachable IMIS cannot hold up or undo the decision; retried with backoff.
 */
class ReportSelectionToImis implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public int $vacancyId, public int $applicationId) {}

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(ImisPlantilla $imis): void
    {
        $vacancy = Vacancy::withTrashed()->find($this->vacancyId);
        $application = Application::withTrashed()->with('applicant.user')->find($this->applicationId);

        if (! $imis->enabled() || ! $vacancy?->isLinkedToImis() || ! $application) {
            return;
        }

        $imis->reportSelection($vacancy, $application);
    }
}
