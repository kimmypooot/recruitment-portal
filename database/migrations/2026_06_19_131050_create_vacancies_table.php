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
            Schema::create('vacancies', function (Blueprint $table) {
        $table->id();
        $table->string('position_title');
        $table->string('item_number')->unique();
        $table->unsignedTinyInteger('salary_grade');
        $table->string('plantilla_number')->nullable();
        $table->string('place_of_assignment');
        $table->text('education_req');
        $table->text('experience_req');
        $table->text('training_req');
        $table->text('eligibility_req');
        $table->enum('status', ['draft','published','closed','filled'])->default('draft');
        $table->foreignId('posted_by')->constrained('users')->onDelete('cascade');
        $table->timestamp('published_at')->nullable();
        $table->timestamp('deadline_at')->nullable();
        $table->timestamps();      // created_at, updated_at
        $table->softDeletes();     // deleted_at (safe deletion)
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
