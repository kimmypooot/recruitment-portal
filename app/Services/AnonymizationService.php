<?php

namespace App\Services;

use App\Models\AnonymizationToken;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Support\Str;

class AnonymizationService
{
    public function generateForVacancy(Vacancy $vacancy): void
    {
        $applications = $vacancy->applications()
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->get();

        foreach ($applications as $application) {
            $this->generateForApplication($application, $vacancy);
        }
    }

    public function generateForApplication(Application $application, ?Vacancy $vacancy = null): AnonymizationToken
    {
        $resolvedVacancy = $vacancy ?? $application->vacancy;

        return AnonymizationToken::firstOrCreate(
            ['application_id' => $application->id],
            ['token' => $this->uniqueToken($resolvedVacancy)]
        );
    }

    public function unmaskVacancy(Vacancy $vacancy, User $unmaskedBy): void
    {
        AnonymizationToken::whereIn(
            'application_id',
            $vacancy->applications()->pluck('id')
        )
            ->whereNull('unmasked_at')
            ->update([
                'unmasked_at' => now(),
                'unmasked_by' => $unmaskedBy->id,
            ]);
    }

    public function getAnonymizedView(Application $application): array
    {
        $token = $application->anonymizationToken;

        if (!$token || $token->isUnmasked()) {
            $profile = $application->applicant;
            return [
                'token'       => $token?->token,
                'unmasked'    => true,
                'first_name'  => $profile?->first_name,
                'last_name'   => $profile?->last_name,
                'middle_name' => $profile?->middle_name,
            ];
        }

        return [
            'token'    => $token->token,
            'unmasked' => false,
        ];
    }

    /**
     * Generate a blind code in the format: {POSITION_CODE}-{MMDD}-{NNN}
     *
     * POSITION_CODE: first segment of plantilla_no (e.g. "AO2-001-2026" → "AO2")
     * MMDD:          4-digit month + day of publication (e.g. "0326" for March 26)
     * NNN:           sequential 3-digit increment based on applicant count (001, 002…)
     */
    private function uniqueToken(Vacancy $vacancy): string
    {
        // Extract position code — everything before the first dash in plantilla_no
        $itemNum = $vacancy->plantilla_no ?? 'POS';
        $posCode = Str::before($itemNum, '-') ?: $itemNum;

        // Use publication date; fall back to creation date if not yet published
        $pubDate = $vacancy->published_at ?? $vacancy->created_at;
        $prefix  = $posCode . '-' . $pubDate->format('md');

        // Determine the next sequential number based on existing tokens for this vacancy
        $existingCount = AnonymizationToken::whereIn(
            'application_id',
            $vacancy->applications()->pluck('id')
        )
            ->where('token', 'like', "{$prefix}-%")
            ->count();

        $seq = str_pad($existingCount + 1, 3, '0', STR_PAD_LEFT);
        $token = "{$prefix}-{$seq}";

        // Ensure global uniqueness (safety net)
        while (AnonymizationToken::where('token', $token)->exists()) {
            $existingCount++;
            $seq = str_pad($existingCount + 1, 3, '0', STR_PAD_LEFT);
            $token = "{$prefix}-{$seq}";
        }

        return $token;
    }
}
