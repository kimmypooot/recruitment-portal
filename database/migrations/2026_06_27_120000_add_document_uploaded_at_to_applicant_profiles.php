<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->timestamp('pds_uploaded_at')->nullable()->after('pds_path');
            $table->timestamp('app_letter_uploaded_at')->nullable()->after('app_letter_path');
            $table->timestamp('coe_uploaded_at')->nullable()->after('coe_path');
            $table->timestamp('tor_uploaded_at')->nullable()->after('tor_path');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropColumn(['pds_uploaded_at', 'app_letter_uploaded_at', 'coe_uploaded_at', 'tor_uploaded_at']);
        });
    }
};
