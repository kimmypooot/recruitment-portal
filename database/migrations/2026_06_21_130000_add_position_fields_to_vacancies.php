<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->decimal('monthly_salary', 12, 2)->nullable()->after('salary_grade');
            $table->string('position_level')->nullable()->after('monthly_salary');
            $table->boolean('is_anticipated_vacancy')->default(false)->after('position_level');
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropColumn(['monthly_salary', 'position_level', 'is_anticipated_vacancy']);
        });
    }
};
