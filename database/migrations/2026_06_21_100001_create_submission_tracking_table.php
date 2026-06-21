<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('deadline_type')->default('csc_submission'); // 30-day rule
            $table->timestamp('due_at');
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['pending', 'submitted', 'overdue'])->default('pending');
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'deadline_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_tracking');
    }
};
