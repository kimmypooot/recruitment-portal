<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('middle_name');
            $table->string('civil_status')->nullable()->after('gender');
            $table->date('birthday')->nullable()->after('civil_status');
            $table->string('religion')->nullable()->after('birthday');
            $table->string('region')->nullable()->after('address');
            $table->string('province')->nullable()->after('region');
            $table->string('city_municipality')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('city_municipality');
            $table->string('mobile_number')->nullable()->after('contact_number');
            $table->string('eligibility')->nullable();
            $table->string('eligibility_other')->nullable();
            $table->string('indigenous_group')->nullable();
            $table->string('pwd')->nullable();
            $table->string('solo_parent')->nullable();
            $table->string('pds_path')->nullable();
            $table->string('app_letter_path')->nullable();
            $table->string('ipcr_path')->nullable();
            $table->string('coe_path')->nullable();
            $table->string('tor_path')->nullable();
            $table->timestamp('profile_completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'gender', 'civil_status', 'birthday', 'religion',
                'region', 'province', 'city_municipality', 'barangay',
                'mobile_number', 'eligibility', 'eligibility_other',
                'indigenous_group', 'pwd', 'solo_parent',
                'pds_path', 'app_letter_path', 'ipcr_path', 'coe_path', 'tor_path',
                'profile_completed_at',
            ]);
        });
    }
};
