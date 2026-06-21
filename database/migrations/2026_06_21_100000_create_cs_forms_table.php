<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cs_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
            $table->enum('form_type', ['33A', '33B', 'form1']);
            $table->string('file_path');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('submitted_to_csc_at')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'form_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cs_forms');
    }
};
