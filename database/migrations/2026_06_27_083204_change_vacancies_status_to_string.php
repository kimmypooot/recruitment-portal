<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->string('status', 50)->default('draft')->change();
        });
    }

    public function down(): void
    {
        // MySQL ENUM cannot be restored via schema builder; this is intentionally a one-way change.
    }
};
