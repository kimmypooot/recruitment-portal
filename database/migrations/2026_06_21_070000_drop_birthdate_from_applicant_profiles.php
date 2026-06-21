<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            // 'birthday' (added 2026-06-21) is the canonical column; drop the original duplicate
            if (Schema::hasColumn('applicant_profiles', 'birthdate')) {
                $table->dropColumn('birthdate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->after('middle_name');
        });
    }
};
