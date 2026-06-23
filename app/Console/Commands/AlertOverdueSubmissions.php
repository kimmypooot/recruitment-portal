<?php

namespace App\Console\Commands;

use App\Models\SubmissionTracking;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AlertOverdueSubmissions extends Command
{
    protected $signature   = 'submissions:alert-overdue';
    protected $description = 'Mark overdue CSC submission deadlines and notify HR managers';

    public function handle(): void
    {
        $overdue = SubmissionTracking::where('status', 'pending')
            ->where('due_at', '<', now())
            ->with(['application.applicant:id,first_name,last_name', 'vacancy:id,position_title'])
            ->get();

        if ($overdue->isEmpty()) {
            $this->info('No overdue submissions found.');
            return;
        }

        // Update status to overdue
        SubmissionTracking::where('status', 'pending')
            ->where('due_at', '<', now())
            ->update(['status' => 'overdue']);

        // Notify HR managers
        $hrManagers = User::whereIn('role', ['hr-manager', 'admin'])->pluck('email')->toArray();

        $lines = $overdue->map(fn ($t) =>
            "• " . trim(($t->application->applicant->first_name ?? '') . ' ' . ($t->application->applicant->last_name ?? ''))
            . " — " . ($t->vacancy->position_title ?? '?')
            . " (due " . $t->due_at?->format('M d, Y') . ")"
        )->join("\n");

        foreach ($hrManagers as $email) {
            Mail::raw(
                "The following appointments have exceeded the 30-day CSC submission deadline:\n\n{$lines}\n\nPlease submit the required CS Forms to CSC immediately.",
                fn ($msg) => $msg->to($email)->subject('[URGENT] Overdue CSC Submission Deadlines — CSC RO VIII')
            );
        }

        $this->info("Marked {$overdue->count()} submission(s) as overdue and notified HR managers.");

        // Also notify 5 days before due
        $upcoming = SubmissionTracking::where('status', 'pending')
            ->whereBetween('due_at', [now(), now()->addDays(5)])
            ->whereNull('last_notified_at')
            ->orWhere('last_notified_at', '<', now()->subDays(2))
            ->with(['application.applicant:id,first_name,last_name', 'vacancy:id,position_title'])
            ->get();

        if ($upcoming->isNotEmpty()) {
            $upcomingLines = $upcoming->map(fn ($t) =>
                "• " . trim(($t->application->applicant->first_name ?? '') . ' ' . ($t->application->applicant->last_name ?? ''))
                . " — " . ($t->vacancy->position_title ?? '?')
                . " (due in " . abs($t->daysRemaining()) . " days)"
            )->join("\n");

            foreach ($hrManagers as $email) {
                Mail::raw(
                    "Reminder: The following appointments are due for CSC submission within 5 days:\n\n{$upcomingLines}",
                    fn ($msg) => $msg->to($email)->subject('[Reminder] Upcoming CSC Submission Deadlines')
                );
            }

            SubmissionTracking::whereIn('id', $upcoming->pluck('id'))
                ->update(['last_notified_at' => now()]);
        }
    }
}
