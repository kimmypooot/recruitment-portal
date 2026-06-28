<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('submission_tracking');
        Schema::dropIfExists('compliance_deadlines');
    }

    public function down(): void
    {
        Schema::create('compliance_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->onDelete('cascade');
            $table->string('deadline_type');
            $table->timestamp('due_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('submission_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->string('deadline_type')->default('csc_submission');
            $table->timestamp('due_at');
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['pending', 'submitted', 'overdue'])->default('pending');
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();
            $table->unique(['application_id', 'deadline_type']);
        });
    }
};
