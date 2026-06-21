<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->string('competency_key', 64)->unique();
            $table->string('competency_name');
            $table->enum('competency_group', ['Core', 'Organizational', 'Leadership', 'Technical']);
            $table->tinyInteger('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competencies');
    }
};
