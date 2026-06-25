<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('background_investigation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('investigator_name');
            $table->string('investigator_email');
            $table->string('token', 64)->unique();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->text('on_competencies')->nullable();
            $table->text('on_performance')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('background_investigation_reports');
    }
};
