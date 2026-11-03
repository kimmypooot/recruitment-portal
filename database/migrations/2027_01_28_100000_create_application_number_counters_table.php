<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The next applicant number for each application-number prefix
        // (POSITION-ITEM-MM-YYYY). Read and bumped under a row lock when an
        // application is submitted, so two applicants never get the same one.
        Schema::create('application_number_counters', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 80)->unique();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        // Application numbers are longer than the old random CSC-XXXXXXXX codes
        // (e.g. HRMOIII-125-11-2026-0001). Existing codes are left as they are.
        Schema::table('applications', function (Blueprint $table) {
            $table->string('reference_code', 60)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_number_counters');
        // reference_code stays wide: narrowing it could truncate numbers already issued.
    }
};
