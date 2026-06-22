<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Copy contact_number → mobile_number where mobile_number is not already set
        DB::table('applicant_profiles')
            ->whereNotNull('contact_number')
            ->where(function ($q) {
                $q->whereNull('mobile_number')->orWhere('mobile_number', '');
            })
            ->update(['mobile_number' => DB::raw('contact_number')]);

        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropColumn('contact_number');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->string('contact_number')->nullable()->after('address');
        });

        // Restore contact_number from mobile_number on rollback
        DB::table('applicant_profiles')
            ->whereNotNull('mobile_number')
            ->update(['contact_number' => DB::raw('mobile_number')]);
    }
};
