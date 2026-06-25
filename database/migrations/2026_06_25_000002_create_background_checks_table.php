<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('background_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->foreignId('checked_by')->constrained('users')->onDelete('cascade');
            $table->boolean('employment_verified')->nullable();
            $table->boolean('education_verified')->nullable();
            $table->boolean('character_ref_verified')->nullable();
            $table->boolean('nbi_clearance')->nullable();
            $table->enum('background_result', ['clear', 'minor_issues', 'significant_issues'])->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'checked_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('background_checks');
    }
};
