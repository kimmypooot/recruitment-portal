<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores the PDS the system generated, separately from `pds_path`, which holds
 * a copy the applicant filled in and uploaded themselves.
 *
 * Kept as its own column rather than reusing pds_path so it stays visible
 * whether a given applicant's PDS was self-prepared or generated from their
 * profile — that distinction matters when a form is later questioned.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->string('pds_generated_path')->nullable()->after('pds_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropColumn('pds_generated_path');
        });
    }
};
