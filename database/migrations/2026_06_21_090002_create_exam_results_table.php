<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('exam_type', ['TWE', 'CBWE']);
            $table->decimal('raw_score', 5, 2);
            $table->decimal('max_score', 5, 2)->default(100);
            $table->foreignId('encoded_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('encoded_at')->useCurrent();
            $table->timestamps();

            $table->unique(['application_id', 'exam_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
