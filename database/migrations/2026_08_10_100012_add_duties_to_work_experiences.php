<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Section V of CS Form No. 212 (Revised 2026) states: "Description of duties
 * should be indicated in the attached Work Experience Sheet."
 *
 * The duties themselves were never captured anywhere, so the attachment could
 * not be produced from profile data. This is the missing field.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_experiences', function (Blueprint $table) {
            $table->text('duties')->nullable()->after('department_agency');
        });
    }

    public function down(): void
    {
        Schema::table('work_experiences', function (Blueprint $table) {
            $table->dropColumn('duties');
        });
    }
};
