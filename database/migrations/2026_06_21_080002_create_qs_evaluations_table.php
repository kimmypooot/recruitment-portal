<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qs_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->boolean('education_meets')->nullable();
            $table->boolean('experience_meets')->nullable();
            $table->boolean('training_meets')->nullable();
            $table->boolean('eligibility_meets')->nullable();
            $table->boolean('overall_qualified')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'evaluator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qs_evaluations');
    }
};
