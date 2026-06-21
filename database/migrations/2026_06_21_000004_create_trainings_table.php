<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_profile_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('date_from');
            $table->string('date_to')->nullable();
            $table->decimal('hours', 8, 2)->nullable();
            $table->string('ld_type')->nullable();
            $table->string('conducted_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
