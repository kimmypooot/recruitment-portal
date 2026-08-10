<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Section II items 22, 24, 25 — spouse, father, mother's maiden name.
 * Children (item 23) are a repeating list and live in `pds_children`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pds_family_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_profile_id')->unique()->constrained()->onDelete('cascade');

            // 22
            $table->string('spouse_surname')->nullable();
            $table->string('spouse_first_name')->nullable();
            $table->string('spouse_middle_name')->nullable();
            $table->string('spouse_name_extension')->nullable();
            $table->string('spouse_occupation')->nullable();
            $table->string('spouse_employer')->nullable();
            $table->string('spouse_business_address')->nullable();
            $table->string('spouse_telephone')->nullable();

            // 24
            $table->string('father_surname')->nullable();
            $table->string('father_first_name')->nullable();
            $table->string('father_middle_name')->nullable();
            $table->string('father_name_extension')->nullable();

            // 25 — mother's MAIDEN name; no name extension on the form
            $table->string('mother_maiden_surname')->nullable();
            $table->string('mother_maiden_first_name')->nullable();
            $table->string('mother_maiden_middle_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pds_family_backgrounds');
    }
};
