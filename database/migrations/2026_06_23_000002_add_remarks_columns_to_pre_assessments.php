<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_assessments', function (Blueprint $table) {
            $table->text('pds_remarks')->nullable()->after('coe_submitted');
            $table->text('ipcr_remarks')->nullable()->after('pds_remarks');
            $table->text('tor_remarks')->nullable()->after('ipcr_remarks');
            $table->text('coe_remarks')->nullable()->after('tor_remarks');
            $table->text('license_remarks')->nullable()->after('secretariat_remarks');
        });
    }

    public function down(): void
    {
        Schema::table('pre_assessments', function (Blueprint $table) {
            $table->dropColumn(['pds_remarks', 'ipcr_remarks', 'tor_remarks', 'coe_remarks', 'license_remarks']);
        });
    }
};
