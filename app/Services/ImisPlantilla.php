<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * The portal's link to IMIS's plantilla: reads the vacant items a vacancy can
 * be opened on, and reports whom the Appointing Authority selected. Both are
 * server-to-server calls with a bearer token; off while IMIS_API_TOKEN is unset.
 */
class ImisPlantilla
{
    public function enabled(): bool
    {
        return (bool) config('services.imis.api_token') && $this->baseUrl() !== null;
    }

    /**
     * Vacant plantilla items, each with the place of assignment used here
     * (null when the item's office has no counterpart).
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws RuntimeException when IMIS cannot be reached or refuses
     */
    public function vacantPositions(): array
    {
        $response = $this->request()->get('/api/v1/recruitment/vacant-positions');

        if (! $response->successful() || ! is_array($response->json('data'))) {
            throw new RuntimeException('IMIS could not list its vacant positions (HTTP '.$response->status().').');
        }

        return array_map(fn (array $item) => [
            'id' => $item['id'],
            'item_number' => $item['item_number'] ?? null,
            'position_title' => $item['position_title'],
            'salary_grade' => $item['salary_grade'] ?? null,
            'monthly_salary' => $item['monthly_salary'] ?? null,
            'place_of_assignment' => Vacancy::IMIS_OFFICES[$item['office_code'] ?? ''] ?? null,
        ], $response->json('data'));
    }

    /**
     * Tells IMIS who was selected for the item the vacancy was opened on.
     *
     * @throws RuntimeException when IMIS refuses, so a queued job retries
     */
    public function reportSelection(Vacancy $vacancy, Application $application): void
    {
        $user = $application->applicant?->user;

        $response = $this->request()->post("/api/v1/recruitment/items/{$vacancy->imis_plantilla_item_id}/selection", [
            'recruitment_vacancy_id' => $vacancy->id,
            'applicant_name' => $user?->full_name ?: 'Applicant #'.$application->id,
            'applicant_email' => $user?->email,
            'selected_on' => now()->toDateString(),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("IMIS refused the selection for vacancy {$vacancy->id} (HTTP {$response->status()}).");
        }
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl((string) $this->baseUrl())
            ->withToken((string) config('services.imis.api_token'))
            ->acceptJson()->timeout(10);
    }

    private function baseUrl(): ?string
    {
        if ($url = config('services.imis.api_url')) {
            return rtrim((string) $url, '/');
        }

        $parts = parse_url((string) config('services.imis.sso_url'));

        return isset($parts['scheme'], $parts['host'])
            ? $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '')
            : null;
    }
}
