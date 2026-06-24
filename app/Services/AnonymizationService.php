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
     * Generate a blind code in the format: {POSITION_CODE}-{MM}-{YYYY}-{NNN}
     *
     * POSITION_CODE: first segment of item_number (e.g. "AO2-001-2026" → "AO2")
     * MM:            2-digit publication month
     * YYYY:          publication year
     * NNN:           random 3-digit integer (100–999), unique within the vacancy
     */
    private function uniqueToken(Vacancy $vacancy): string
    {
        // Extract position code — everything before the first dash in item_number
        $itemNum = $vacancy->item_number ?? 'POS';
        $posCode = Str::before($itemNum, '-') ?: $itemNum;

        // Use publication date; fall back to creation date if not yet published
        $pubDate = $vacancy->published_at ?? $vacancy->created_at;
        $prefix  = $posCode . '-' . $pubDate->format('m') . '-' . $pubDate->format('Y');

        // Collect 3-digit suffixes already assigned to this vacancy
        $usedInVacancy = AnonymizationToken::whereIn(
            'application_id',
            $vacancy->applications()->pluck('id')
        )
            ->pluck('token')
            ->map(fn ($t) => (int) Str::afterLast($t, '-'))
            ->filter(fn ($n) => $n >= 100 && $n <= 999)
            ->all();

        // Pick a suffix that is unique within the vacancy AND globally
        do {
            $suffix = random_int(100, 999);
            $token  = "{$prefix}-{$suffix}";
        } while (
            in_array($suffix, $usedInVacancy, true) ||
            AnonymizationToken::where('token', $token)->exists()
        );

        return $token;
    }
}
