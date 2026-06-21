<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliberation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['endorsed', 'appointed', 'not_recommended']);
            $table->integer('rank')->nullable(); // ranking among endorsed candidates
            $table->foreignId('decided_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('decided_at')->useCurrent();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['vacancy_id', 'application_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliberation_results');
    }
};
