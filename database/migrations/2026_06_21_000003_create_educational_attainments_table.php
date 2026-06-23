<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educational_attainments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_profile_id')->constrained()->onDelete('cascade');
            $table->string('level'); // elementary, secondary, vocational, college, graduate_studies
            $table->string('school_name');
            $table->string('degree_course')->nullable();
            $table->string('period_from')->nullable();
            $table->string('period_to')->nullable();
            $table->string('units_earned')->nullable();
            $table->string('year_graduated')->nullable();
            $table->string('honors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educational_attainments');
    }
};
