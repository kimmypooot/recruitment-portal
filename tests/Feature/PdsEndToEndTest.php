<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\PdsOtherInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Walks the whole PDS the way the profile UI does — every section written
 * through its own HTTP endpoint — and then generates the workbook.
 *
 * This is the test that catches a mismatch between what the editors send and
 * what the completeness checker and generator expect.
 */
class PdsEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_applicant_can_fill_the_whole_form_through_the_api_and_generate_it(): void
    {
        $user = User::factory()->applicant()->create([
            'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'middle_name' => 'Reyes',
        ]);
        ApplicantProfile::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum');

        // Nothing filled in: generation must be refused.
        $this->getJson('/api/profile/pds/download')->assertStatus(422);

        // --- Section I, via the profile endpoint the PDS Details tab uses ---
        $this->putJson('/api/profile', [
            'gender'        => 'Male',
            'civil_status'  => 'Single',
            'birthday'      => '1990-03-17',
            'mobile_number' => '09171234567',
            'region'        => 'Region VIII',
            'eligibility'   => 'Career Service Professional',

            'place_of_birth'         => 'Tacloban City',
            'citizenship'            => 'filipino',
            'blood_type'             => 'O+',
            'philsys_number'         => '2059-8569-1428-1039',
            'res_city_municipality'  => 'Tacloban City',
            'res_province'           => 'Leyte',
            'perm_city_municipality' => 'Tacloban City',
            'perm_province'          => 'Leyte',
        ])->assertOk();

        // --- Section II ---
        $this->putJson('/api/profile/pds/family-background', [
            'father_surname'           => 'Dela Cruz',
            'father_first_name'        => 'Pedro',
            'mother_maiden_surname'    => 'Reyes',
            'mother_maiden_first_name' => 'Maria',
        ])->assertOk();

        $this->postJson('/api/profile/pds/children', [
            'full_name' => 'Ana Dela Cruz', 'date_of_birth' => '2015-04-02',
        ])->assertCreated();

        // --- Section III, via the existing profile endpoints ---
        foreach ([['elementary', 'Tacloban Central ES'], ['secondary', 'Leyte National HS']] as [$level, $school]) {
            $this->postJson('/api/profile/education', [
                'level' => $level, 'school_name' => $school,
            ])->assertSuccessful();
        }

        // --- Sections IV, VI, VIII ---
        $this->postJson('/api/profile/pds/eligibilities', [
            'eligibility_name' => 'Career Service Professional',
            'rating'           => '85.50',
            'examination_date' => '2019-08-04',
        ])->assertCreated();

        $this->postJson('/api/profile/pds/voluntary-works', [
            'organization_name_address' => 'Philippine Red Cross, Tacloban',
            'number_of_hours'           => 40,
        ])->assertCreated();

        $this->postJson('/api/profile/pds/other-info', [
            'kind' => PdsOtherInfo::KIND_MEMBERSHIP, 'value' => 'PASUC',
        ])->assertCreated();

        // --- Page 4 ---
        $this->putJson('/api/profile/pds/declaration', [
            'q34a_related_third_degree'  => false,
            'q34b_related_fourth_degree' => false,
            'q35a_guilty_admin_offense'  => false,
            'q35b_criminally_charged'    => false,
            'q36_convicted'              => true,
            'q36_details'                => 'Dismissed 2015',
            'q37_separated_from_service' => false,
            'q38a_election_candidate'    => false,
            'q38b_resigned_to_campaign'  => false,
            'q39_immigrant_status'       => false,
            'q40a_indigenous_group'      => false,
            'q40b_pwd'                   => false,
            'q40c_solo_parent'           => false,
            'government_id_type'         => 'PRC',
            'government_id_number'       => '0089934',
        ])->assertOk();

        foreach (range(1, 3) as $i) {
            $this->postJson('/api/profile/pds/references', [
                'name'                => "Reference {$i}",
                'contact_no_or_email' => "reference{$i}@example.com",
            ])->assertCreated();
        }

        // --- Everything is now answered ---
        $completeness = $this->getJson('/api/profile/pds/completeness')->assertOk()->json();

        $this->assertTrue(
            $completeness['can_generate'],
            'still blocking: '.implode(', ', $completeness['blocking'])
        );

        $response = $this->get('/api/profile/pds/download?format=xlsx');
        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition') ?? '');
    }

    public function test_leaving_one_declaration_unanswered_still_blocks_generation(): void
    {
        $user = User::factory()->applicant()->create();
        ApplicantProfile::factory()->complete()->pdsPersonalInfo()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum');

        $this->putJson('/api/profile/pds/declaration', [
            'q34a_related_third_degree' => false,
        ])->assertOk();

        $result = $this->getJson('/api/profile/pds/completeness')->json();

        $this->assertFalse($result['can_generate']);
        $this->assertContains('declarations', $result['blocking']);
        $this->assertNotContains(
            'q34a_related_third_degree',
            $result['sections']['declarations']['missing'],
            'an answered question must not still count as missing'
        );
    }
}
