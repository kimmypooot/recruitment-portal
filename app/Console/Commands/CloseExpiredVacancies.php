<?php

namespace App\Console\Commands;

use App\Models\ComplianceDeadline;
use App\Models\Vacancy;
use App\Services\AuditLog;
use Illuminate\Console\Command;

class CloseExpiredVacancies extends Command
{
    protected $signature   = 'vacancies:close-expired';
    protected $description = 'Auto-close published vacancies that have passed their deadline';

    public function handle(): int
    {
        $expired = Vacancy::where('status', 'published')
            ->where('deadline_at', '<', now())
            ->get();

        foreach ($expired as $vacancy) {
            $vacancy->update(['status' => 'closed']);

            // Mark the publication compliance deadline as completed
            ComplianceDeadline::where('vacancy_id', $vacancy->id)
                ->where('deadline_type', 'publication')
                ->whereNull('completed_at')
                ->update(['completed_at' => now()]);

            AuditLog::record('vacancy_auto_closed', $vacancy);

            $this->line("Closed: [{$vacancy->id}] {$vacancy->position_title}");
        }

        $this->info("Processed {$expired->count()} expired vacancies.");

        return self::SUCCESS;
    }
}
