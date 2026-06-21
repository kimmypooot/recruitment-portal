<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancy_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->string('competency_key', 64);
            $table->foreign('competency_key')->references('competency_key')->on('competencies')->cascadeOnDelete();
            $table->tinyInteger('competency_level')->default(1)->comment('1=Basic 2=Intermediate 3=Advanced 4=Superior');
            $table->unique(['vacancy_id', 'competency_key']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancy_competencies');
    }
};
