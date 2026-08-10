<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Page 4 of CS Form No. 212, Revised 2026 — questions 34 to 42.
 *
 * Every yes/no is nullable on purpose: the PDS distinguishes "answered No" from
 * "not yet answered", and completeness checking depends on telling them apart.
 *
 * Kept off `applicant_profiles` deliberately — this is a sworn attestation with
 * its own lifecycle (re-affirmed per submission), not profile data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pds_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_profile_id')->unique()->constrained()->onDelete('cascade');

            // 34 — consanguinity / affinity to the appointing or recommending authority
            $table->boolean('q34a_related_third_degree')->nullable();
            $table->boolean('q34b_related_fourth_degree')->nullable();
            $table->text('q34_details')->nullable();

            // 35a — administrative offence
            $table->boolean('q35a_guilty_admin_offense')->nullable();
            $table->text('q35a_details')->nullable();

            // 35b — criminally charged before any court
            $table->boolean('q35b_criminally_charged')->nullable();
            $table->date('q35b_date_filed')->nullable();
            $table->string('q35b_case_status')->nullable();

            // 36 — convicted of any crime or violation
            $table->boolean('q36_convicted')->nullable();
            $table->text('q36_details')->nullable();

            // 37 — separated from the service (resignation, retirement, dropped from
            // rolls, dismissal, termination, end of term, finished contract, phased out)
            $table->boolean('q37_separated_from_service')->nullable();
            $table->text('q37_details')->nullable();

            // 38 — election candidacy / resignation to campaign
            $table->boolean('q38a_election_candidate')->nullable();
            $table->text('q38a_details')->nullable();
            $table->boolean('q38b_resigned_to_campaign')->nullable();
            $table->text('q38b_details')->nullable();

            // 39 — immigrant or permanent resident of another country
            $table->boolean('q39_immigrant_status')->nullable();
            $table->string('q39_country')->nullable();

            // 40 — RA 8371 / RA 7277 as amended / RA 11861
            $table->boolean('q40a_indigenous_group')->nullable();
            $table->string('q40a_group_name')->nullable();
            $table->boolean('q40b_pwd')->nullable();
            $table->string('q40b_id_no')->nullable();
            $table->boolean('q40c_solo_parent')->nullable();
            $table->string('q40c_id_no')->nullable();

            // 42 — government issued ID presented to the person administering the oath
            $table->string('government_id_type')->nullable();
            $table->string('government_id_number')->nullable();
            $table->date('government_id_issuance_date')->nullable();
            $table->string('government_id_issuance_place')->nullable();

            $table->date('date_accomplished')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('right_thumbmark_path')->nullable();
            $table->timestamp('attested_at')->nullable();

            $table->timestamps();
        });

        // Seed a declaration row per existing profile, carrying over the three
        // Q40 answers already captured on `applicant_profiles` as "Yes"/"No".
        $now = now();
        $rows = DB::table('applicant_profiles')
            ->select('id', 'indigenous_group', 'pwd', 'solo_parent')
            ->get()
            ->map(fn ($p) => [
                'applicant_profile_id' => $p->id,
                'q40a_indigenous_group' => self::toBool($p->indigenous_group),
                'q40b_pwd' => self::toBool($p->pwd),
                'q40c_solo_parent' => self::toBool($p->solo_parent),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($rows !== []) {
            DB::table('pds_declarations')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pds_declarations');
    }

    private static function toBool(?string $value): ?bool
    {
        return match (strtolower(trim((string) $value))) {
            'yes', '1', 'true' => true,
            'no', '0', 'false' => false,
            default => null,
        };
    }
};
