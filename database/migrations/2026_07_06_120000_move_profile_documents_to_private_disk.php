<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        // Profile documents (PDS, TOR, COE, etc.) contain sensitive PII and must not
        // live under storage/app/public, which is web-accessible via the /storage symlink.
        $this->relocate(
            storage_path('app/public/profile-documents'),
            storage_path('app/private/profile-documents'),
        );
    }

    public function down(): void
    {
        $this->relocate(
            storage_path('app/private/profile-documents'),
            storage_path('app/public/profile-documents'),
        );
    }

    private function relocate(string $from, string $to): void
    {
        if (! File::isDirectory($from)) {
            return;
        }

        File::ensureDirectoryExists(dirname($to));

        // rename() can fail silently (e.g. on Windows or across filesystems);
        // fall back to copy + delete and verify before removing the source.
        if (! File::moveDirectory($from, $to, true)) {
            if (! File::copyDirectory($from, $to)) {
                throw new RuntimeException("Failed to relocate {$from} to {$to}");
            }
            File::deleteDirectory($from);
        }
    }
};
