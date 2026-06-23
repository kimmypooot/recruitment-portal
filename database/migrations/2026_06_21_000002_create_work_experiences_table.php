<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_profile_id')->constrained()->onDelete('cascade');
            $table->string('position_title');
            $table->string('department_agency');
            $table->decimal('monthly_salary', 12, 2)->nullable();
            $table->string('salary_grade')->nullable();
            $table->string('appointment_status')->nullable();
            $table->boolean('government_service')->default(false);
            $table->string('date_from');
            $table->string('date_to')->nullable();
            $table->boolean('is_present')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};
