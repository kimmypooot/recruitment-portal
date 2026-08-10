<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fields the "CS Form No. 212 Attachment - Work Experience Sheet" asks for that
 * the PDS itself does not.
 *
 * The attachment lists, per entry: Duration, Position, Name of Office/Unit,
 * Immediate Supervisor, Name of Agency/Organization and Location, a list of
 * Accomplishments and Contributions, and a Summary of Actual Duties.
 *
 * Duration, position, agency and duties already existed; these four did not.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_experiences', function (Blueprint $table) {
            $table->string('office_unit')->nullable()->after('department_agency');
            $table->string('agency_location')->nullable()->after('office_unit');
            $table->string('immediate_supervisor')->nullable()->after('agency_location');
            $table->text('accomplishments')->nullable()->after('duties');
        });
    }

    public function down(): void
    {
        Schema::table('work_experiences', function (Blueprint $table) {
            $table->dropColumn(['office_unit', 'agency_location', 'immediate_supervisor', 'accomplishments']);
        });
    }
};
