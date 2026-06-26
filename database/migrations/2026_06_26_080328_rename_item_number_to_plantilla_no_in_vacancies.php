<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropUnique(['item_number']);
            $table->dropColumn('plantilla_number');
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->renameColumn('item_number', 'plantilla_no');
            $table->string('plantilla_no')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropUnique(['plantilla_no']);
            $table->renameColumn('plantilla_no', 'item_number');
            $table->string('item_number')->unique()->change();
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->string('plantilla_number')->nullable();
        });
    }
};
