<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anonymization_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->onDelete('cascade');
            $table->string('token', 12)->unique(); // e.g. CSC-A3X9KL
            $table->timestamp('unmasked_at')->nullable();
            $table->foreignId('unmasked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anonymization_tokens');
    }
};
