<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bei_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            // JSON: { "professionalism_ethics": 4, "results_focus": 3, ... }
            $table->json('competency_scores');
            $table->decimal('total_rating', 4, 2)->nullable(); // average of competency scores
            $table->text('remarks')->nullable();
            $table->timestamp('rated_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'evaluator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bei_ratings');
    }
};
