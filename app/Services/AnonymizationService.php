<?php

declare(strict_types=1);

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
     * Generate a blind code in the format: {POSITION_CODE}-{RANDOM}
     *
     * POSITION_CODE: first segment of plantilla_no (e.g. "AO2-001-2026" → "AO2") —
     *                purely for the board's own grouping/readability; it's already
     *                public on the vacancy posting so it carries no identity signal.
     * RANDOM:        cryptographically random 6-character suffix. Deliberately NOT
     *                derived from publication date or submission order — both are
     *                either public or inferable, which would let a board member
     *                reconstruct the identity mapping the blind code exists to hide.
     */
    private function uniqueToken(Vacancy $vacancy): string
    {
        // Extract position code — everything before the first dash in plantilla_no
        $itemNum = $vacancy->plantilla_no ?? 'POS';
        $posCode = Str::before($itemNum, '-') ?: $itemNum;

        do {
            $token = $posCode . '-' . Str::upper(Str::random(6));
        } while (AnonymizationToken::where('token', $token)->exists());

        return $token;
    }
}
