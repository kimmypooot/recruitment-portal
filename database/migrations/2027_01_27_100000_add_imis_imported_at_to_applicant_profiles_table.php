<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // When an IMIS employee last pulled their PDS and 201-file documents into this profile.
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->timestamp('imis_imported_at')->nullable()->after('profile_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropColumn('imis_imported_at');
        });
    }
};
