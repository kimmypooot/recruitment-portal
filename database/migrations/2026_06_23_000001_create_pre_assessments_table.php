<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->onDelete('cascade');

            // Completeness of documentary requirements
            $table->boolean('pds_submitted')->nullable();
            $table->boolean('ipcr_submitted')->nullable(); // null = N/A
            $table->boolean('tor_submitted')->nullable();
            $table->boolean('coe_submitted')->nullable();
            $table->boolean('requirements_complete')->nullable();
            $table->text('requirements_remarks')->nullable(); // mandatory when incomplete
            $table->text('secretariat_remarks')->nullable();

            // HRD assessment on qualification standards (QS)
            $table->boolean('education_meets')->nullable();
            $table->boolean('license_meets')->nullable(); // null = N/A
            $table->boolean('experience_meets')->nullable();
            $table->boolean('training_meets')->nullable();
            $table->boolean('eligibility_meets')->nullable();
            $table->boolean('hrd_assessment')->nullable(); // overall HRD assessment
            $table->text('hrd_remarks')->nullable();

            // HRMPSB consensus
            $table->boolean('consensus')->nullable();

            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_assessments');
    }
};
