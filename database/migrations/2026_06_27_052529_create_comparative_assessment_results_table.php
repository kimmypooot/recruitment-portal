<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comparative_assessment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparative_assessment_results');
    }
};
