<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The PDS editors add an empty row for the applicant to type into, so the
 * identifying column of each repeating section has to accept NULL.
 *
 * These were NOT NULL, which made adding a reference fail outright and pushed
 * the other sections into seeding placeholder text like "New eligibility" —
 * text that would then print onto an official CSC form if the row was left
 * unfilled. PdsDataAssembler drops blank rows, so they never reach the document.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'pds_children'                 => ['full_name', 255],
        'civil_service_eligibilities'  => ['eligibility_name', 255],
        'voluntary_works'              => ['organization_name_address', 500],
        'pds_other_infos'              => ['value', 500],
        'pds_references'               => ['name', 255],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => [$column, $length]) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $length) {
                $blueprint->string($column, $length)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Blank rows created while this was in force would violate the restored
        // constraint, so clear them first.
        foreach (self::COLUMNS as $table => [$column, $length]) {
            \Illuminate\Support\Facades\DB::table($table)->whereNull($column)->delete();

            Schema::table($table, function (Blueprint $blueprint) use ($column, $length) {
                $blueprint->string($column, $length)->nullable(false)->change();
            });
        }
    }
};
