<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            // The IMIS plantilla item this vacancy was opened on. Null for a vacancy
            // entered by hand (e.g. a Contract of Service position, which has no item).
            $table->unsignedBigInteger('imis_plantilla_item_id')->nullable()->after('plantilla_no')->index();
            $table->string('employment_type', 30)->default('plantilla')->after('imis_plantilla_item_id');
            // Contract of Service positions have no item number or salary grade.
            // (The unique index on plantilla_no stays; several nulls are allowed.)
            $table->string('plantilla_no')->nullable()->change();
            $table->unsignedTinyInteger('salary_grade')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropColumn(['imis_plantilla_item_id', 'employment_type']);
        });
    }
};
