<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Section IV item 27 — CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/BAR)/UNDER SPECIAL
 * LAWS/CATEGORY II/IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL.
 *
 * Supersedes the single `applicant_profiles.eligibility` string for PDS purposes;
 * that column stays because vacancy screening still reads it. Backfilled below.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civil_service_eligibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_profile_id')->constrained()->onDelete('cascade');
            $table->string('eligibility_name');
            $table->string('rating')->nullable();
            $table->date('examination_date')->nullable();
            $table->string('examination_place')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_valid_until')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            // Explicit name: the generated one exceeds MySQL's 64-char identifier limit.
            $table->index(['applicant_profile_id', 'sort_order'], 'cs_eligibilities_profile_sort_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civil_service_eligibilities');
    }
};
