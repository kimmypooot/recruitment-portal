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
            $this->generateForApplication($application);
        }
    }

    public function generateForApplication(Application $application): AnonymizationToken
    {
        return AnonymizationToken::firstOrCreate(
            ['application_id' => $application->id],
            ['token' => $this->uniqueToken()]
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
            // Identity revealed — return full profile data
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

    private function uniqueToken(): string
    {
        do {
            $token = 'CSC-' . strtoupper(Str::random(6));
        } while (AnonymizationToken::where('token', $token)->exists());

        return $token;
    }
}
