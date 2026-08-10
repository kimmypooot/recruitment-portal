<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section I (Personal Information) of CS Form No. 212, Revised 2026.
 *
 * The pre-existing region/province/city_municipality/barangay columns are kept
 * as-is because other reporting code reads them; the PDS-shaped residential
 * address is added alongside and backfilled from them. The PDS has no "region"
 * field, so `region` stays internal-only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            // Items 4, 6, 9
            $table->string('place_of_birth')->nullable()->after('birthday');
            $table->string('civil_status_other')->nullable()->after('civil_status');
            $table->decimal('height_m', 4, 2)->nullable()->after('place_of_birth');
            $table->decimal('weight_kg', 5, 2)->nullable()->after('height_m');
            $table->string('blood_type', 10)->nullable()->after('weight_kg');

            // Item 16 — citizenship. Stored as plain strings; the project has
            // already been burned by enum-altering migrations (vacancies.status).
            $table->string('citizenship', 20)->nullable()->after('blood_type');            // filipino|dual
            $table->string('citizenship_basis', 30)->nullable()->after('citizenship');     // by_birth|by_naturalization
            $table->string('dual_citizenship_country')->nullable()->after('citizenship_basis');

            // Items 10-15 — government IDs. umid_id_no and philsys_number are new in 2026.
            $table->string('gsis_id_no')->nullable();
            $table->string('pagibig_id_no')->nullable();
            $table->string('philhealth_no')->nullable();
            $table->string('sss_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('umid_id_no')->nullable();
            $table->string('philsys_number')->nullable();
            $table->string('agency_employee_no')->nullable();

            // Item 19
            $table->string('telephone_no')->nullable();

            // Item 17 — residential address
            $table->string('res_house_block_lot')->nullable();
            $table->string('res_street')->nullable();
            $table->string('res_subdivision')->nullable();
            $table->string('res_barangay')->nullable();
            $table->string('res_city_municipality')->nullable();
            $table->string('res_province')->nullable();
            $table->string('res_zip_code', 10)->nullable();

            // Item 18 — permanent address
            $table->string('perm_house_block_lot')->nullable();
            $table->string('perm_street')->nullable();
            $table->string('perm_subdivision')->nullable();
            $table->string('perm_barangay')->nullable();
            $table->string('perm_city_municipality')->nullable();
            $table->string('perm_province')->nullable();
            $table->string('perm_zip_code', 10)->nullable();

            $table->timestamp('pds_generated_at')->nullable();
        });

        // Backfill the residential address from the legacy flat columns.
        DB::table('applicant_profiles')->update([
            'res_barangay' => DB::raw('barangay'),
            'res_city_municipality' => DB::raw('city_municipality'),
            'res_province' => DB::raw('province'),
        ]);
    }

    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'place_of_birth', 'civil_status_other', 'height_m', 'weight_kg', 'blood_type',
                'citizenship', 'citizenship_basis', 'dual_citizenship_country',
                'gsis_id_no', 'pagibig_id_no', 'philhealth_no', 'sss_no', 'tin_no',
                'umid_id_no', 'philsys_number', 'agency_employee_no',
                'telephone_no',
                'res_house_block_lot', 'res_street', 'res_subdivision', 'res_barangay',
                'res_city_municipality', 'res_province', 'res_zip_code',
                'perm_house_block_lot', 'perm_street', 'perm_subdivision', 'perm_barangay',
                'perm_city_municipality', 'perm_province', 'perm_zip_code',
                'pds_generated_at',
            ]);
        });
    }
};
