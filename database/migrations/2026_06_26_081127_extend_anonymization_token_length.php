<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anonymization_tokens', function (Blueprint $table) {
            $table->dropUnique(['token']);
        });

        Schema::table('anonymization_tokens', function (Blueprint $table) {
            $table->string('token', 20)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('anonymization_tokens', function (Blueprint $table) {
            $table->dropUnique(['token']);
        });

        Schema::table('anonymization_tokens', function (Blueprint $table) {
            $table->string('token', 12)->unique()->change();
        });
    }
};
