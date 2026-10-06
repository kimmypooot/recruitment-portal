<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * IMIS is where HR staff and board members are managed. `imis_user_id` ties
     * a recruitment account to its IMIS account (set on the first sign-in from
     * IMIS). Applicants are external and never have one.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('imis_user_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['imis_user_id']);
            $table->dropColumn('imis_user_id');
        });
    }
};
