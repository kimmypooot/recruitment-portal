<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\User;
use App\Services\Pds\PdsDataAssembler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Work Experience Sheet attachment asks for details the PDS itself does
 * not: office/unit, immediate supervisor, agency location, accomplishments and
 * a summary of duties. These are captured on work_experiences so both documents
 * render from one source.
 */
class WorkExperienceSheetFieldsTest extends TestCase
{
    use RefreshDatabase;

    private const WES_FIELDS = [
        'office_unit'          => 'Finance and Administrative Service',
        'agency_location'      => 'Palo, Leyte',
        'immediate_supervisor' => 'Maria Estrada',
        'duties'               => 'Responsible for recruitment and selection.',
        'accomplishments'      => "Developed recruitment plan\nDesigned training program",
    ];

    public function test_the_sheet_fields_are_saved_through_the_profile_api(): void
    {
        $user    = User::factory()->applicant()->create();
        $profile = ApplicantProfile::factory()->create(['user_id' => $user->id]);

        $created = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile/experiences', array_merge([
                'position_title'    => 'Administrative Officer III',
                'department_agency' => 'Civil Service Commission',
                'date_from'         => '2020-01-05',
            ], self::WES_FIELDS))
            ->assertCreated()
            ->json();

        $stored = $profile->workExperiences()->findOrFail($created['id']);

        foreach (self::WES_FIELDS as $field => $value) {
            $this->assertSame($value, $stored->{$field}, "{$field} was not saved");
        }
    }

    public function test_the_sheet_fields_reach_the_assembler_unchanged(): void
    {
        $profile = ApplicantProfile::factory()->complete()->create();
        $profile->workExperiences()->create(array_merge([
            'position_title'    => 'Administrative Officer III',
            'department_agency' => 'Civil Service Commission',
            'date_from'         => '2020-01-05',
        ], self::WES_FIELDS));

        $row = app(PdsDataAssembler::class)->assemble($profile)['work_experience'][0];

        foreach (self::WES_FIELDS as $field => $value) {
            $this->assertSame($value, $row[$field]);
        }
    }

    /**
     * The PDS prints N/A for blanks, but the Work Experience Sheet is prose —
     * "N/A" reads badly in a narrative, so these stay empty instead.
     */
    public function test_blank_sheet_fields_stay_empty_rather_than_becoming_not_applicable(): void
    {
        $profile = ApplicantProfile::factory()->complete()->create();
        $profile->workExperiences()->create([
            'position_title'    => 'Clerk',
            'department_agency' => 'DepEd',
            'date_from'         => '2015-01-01',
        ]);

        $row = app(PdsDataAssembler::class)->assemble($profile)['work_experience'][0];

        $this->assertSame('', $row['duties']);
        $this->assertSame('', $row['immediate_supervisor']);
        // The PDS-side columns keep the N/A convention.
        $this->assertSame(PdsDataAssembler::NOT_APPLICABLE, $row['salary_grade']);
    }
}
