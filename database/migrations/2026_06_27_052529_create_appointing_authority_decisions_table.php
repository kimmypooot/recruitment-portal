<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointing_authority_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // appointed, not_appointed
            $table->foreignId('decided_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('decided_at')->useCurrent();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['vacancy_id', 'application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointing_authority_decisions');
    }
};
