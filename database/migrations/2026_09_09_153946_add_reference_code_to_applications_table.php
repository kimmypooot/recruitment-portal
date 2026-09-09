<?php

declare(strict_types=1);

use App\Models\Application;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Applicant-facing tracking code for the passwordless "Track My
     * Application" lookup (docs/strategic-feature-scoping.md). Distinct from
     * the numeric primary key so it isn't guessable/enumerable and reads
     * cleanly when typed by hand or read over the phone: 10 characters from
     * an unambiguous alphabet (no 0/O/1/I) prefixed CSC- for recognizability.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('reference_code', 20)->nullable()->after('id');
        });

        // Backfill existing rows so nothing already submitted is left
        // untrackable. Done here rather than a separate artisan command so
        // it can't be forgotten during deployment.
        Application::withTrashed()->whereNull('reference_code')->orderBy('id')->chunkById(500, function ($applications) {
            foreach ($applications as $application) {
                $application->forceFill(['reference_code' => Application::generateReferenceCode()])->saveQuietly();
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->unique('reference_code');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique(['reference_code']);
            $table->dropColumn('reference_code');
        });
    }
};
