<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * An IMIS employee's verified PDS and the few 201-file documents an application
 * uses, read server-to-server (IMIS RecruitmentEmployeeController) with the same
 * bearer token as ImisPlantilla. Keyed by the IMIS user id the sign-in gave us.
 */
class ImisEmployeeRecords
{
    public function __construct(private readonly ImisPlantilla $imis) {}

    public function enabled(): bool
    {
        return $this->imis->enabled();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws RuntimeException when IMIS cannot be reached, refuses, or has no current employee record
     */
    public function pds(int $imisUserId): array
    {
        $response = $this->imis->request()->get("/api/v1/recruitment/employees/{$imisUserId}/pds");

        $this->ensure($response->status(), $response->successful() && is_array($response->json('data')));

        return $response->json('data');
    }

    /**
     * @return array<int, array<string, mixed>> newest first
     *
     * @throws RuntimeException
     */
    public function documents(int $imisUserId): array
    {
        $response = $this->imis->request()->get("/api/v1/recruitment/employees/{$imisUserId}/documents");

        $this->ensure($response->status(), $response->successful() && is_array($response->json('data')));

        return $response->json('data');
    }

    /**
     * The file's bytes.
     *
     * @throws RuntimeException
     */
    public function file(int $imisUserId, int $documentId): string
    {
        $response = $this->imis->request()->timeout(30)->get("/api/v1/recruitment/employees/{$imisUserId}/documents/{$documentId}/file");

        $this->ensure($response->status(), $response->successful());

        return $response->body();
    }

    private function ensure(int $status, bool $ok): void
    {
        if ($ok) {
            return;
        }

        throw new RuntimeException($status === 404
            ? 'IMIS has no current employee record for you.'
            : "IMIS could not provide your records (HTTP {$status}).");
    }
}
