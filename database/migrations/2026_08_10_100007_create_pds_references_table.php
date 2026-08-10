<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Page 4 item 41 — three references, "person not related by consanguinity or
 * affinity to applicant/appointee". The 2026 form's third column is
 * "CONTACT NO. AND/OR EMAIL" (2017 asked for a telephone number only).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pds_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_profile_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('office_residential_address', 500)->nullable();
            $table->string('contact_no_or_email')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['applicant_profile_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pds_references');
    }
};
