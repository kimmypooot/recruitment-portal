<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\PdsOtherInfo;
use App\Models\User;
use App\Services\Pds\PdsDataAssembler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdsDataAssemblerTest extends TestCase
{
    use RefreshDatabase;

    private function profile(array $attributes = []): ApplicantProfile
    {
        $user = User::factory()->applicant()->create([
            'first_name'  => 'Juan',
            'last_name'   => 'Dela Cruz',
            'middle_name' => 'Reyes',
            'suffix'      => 'Jr.',
        ]);

        return ApplicantProfile::factory()
            ->complete()
            ->pdsPersonalInfo()
            ->create(array_merge(['user_id' => $user->id], $attributes));
    }

    private function assemble(ApplicantProfile $profile): array
    {
        return app(PdsDataAssembler::class)->assemble($profile);
    }

    public function test_blank_fields_become_not_applicable(): void
    {
        $data = $this->assemble($this->profile(['blood_type' => null, 'telephone_no' => '']));

        $this->assertSame('N/A', $data['personal_information']['blood_type']);
        $this->assertSame('N/A', $data['personal_information']['telephone_no']);
        // A profile with no family record at all still renders every field.
        $this->assertSame('N/A', $data['family_background']['mother_maiden']['surname']);
    }

    public function test_names_are_carried_from_the_user_record(): void
    {
        $data = $this->assemble($this->profile())['personal_information'];

        $this->assertSame('Dela Cruz', $data['surname']);
        $this->assertSame('Juan', $data['first_name']);
        $this->assertSame('Reyes', $data['middle_name']);
        $this->assertSame('Jr.', $data['name_extension']);
    }

    public function test_dates_use_the_official_day_month_year_format(): void
    {
        $profile = $this->profile(['birthday' => '1990-03-17']);
        $profile->civilServiceEligibilities()->create([
            'eligibility_name' => 'Career Service Professional',
            'examination_date' => '2019-08-04',
        ]);

        $data = $this->assemble($profile);

        $this->assertSame('17/03/1990', $data['personal_information']['date_of_birth']);
        $this->assertSame('04/08/2019', $data['civil_service_eligibility'][0]['examination_date']);
    }

    public function test_monthly_salary_is_rendered_in_figures(): void
    {
        $profile = $this->profile();
        $profile->workExperiences()->create([
            'position_title'    => 'Administrative Officer',
            'department_agency' => 'Civil Service Commission',
            'monthly_salary'    => 21877,
            'salary_grade'      => '11-1',
            'date_from'         => '2020-01-05',
        ]);

        $row = $this->assemble($profile)['work_experience'][0];

        $this->assertSame('21,877.00', $row['monthly_salary']);
        $this->assertSame('11-1', $row['salary_grade']);
    }

    public function test_work_experience_starts_from_the_most_recent_and_marks_present(): void
    {
        $profile = $this->profile();

        $profile->workExperiences()->create([
            'position_title' => 'Clerk', 'department_agency' => 'DepEd',
            'date_from' => '2015-01-01', 'date_to' => '2018-12-31',
        ]);
        $profile->workExperiences()->create([
            'position_title' => 'Analyst', 'department_agency' => 'CSC',
            'date_from' => '2019-01-01', 'is_present' => true,
        ]);

        $rows = $this->assemble($profile)['work_experience'];

        $this->assertSame('Analyst', $rows[0]['position_title']);
        $this->assertSame('PRESENT', $rows[0]['date_to']);
        $this->assertSame('Clerk', $rows[1]['position_title']);
        $this->assertSame('31/12/2018', $rows[1]['date_to']);
    }

    public function test_government_service_renders_as_y_or_n(): void
    {
        $profile = $this->profile();
        $profile->workExperiences()->create([
            'position_title' => 'Analyst', 'department_agency' => 'CSC',
            'date_from' => '2019-01-01', 'government_service' => true,
        ]);
        $profile->workExperiences()->create([
            'position_title' => 'Encoder', 'department_agency' => 'Acme Inc.',
            'date_from' => '2016-01-01', 'government_service' => false,
        ]);

        $rows = $this->assemble($profile)['work_experience'];

        $this->assertSame('Y', $rows[0]['government_service']);
        $this->assertSame('N', $rows[1]['government_service']);
    }

    public function test_repeating_sections_are_padded_to_the_printed_row_count(): void
    {
        $data = $this->assemble($this->profile());

        foreach (PdsDataAssembler::PRINTED_ROWS as $key => $count) {
            $rows = match ($key) {
                'children'   => $data['family_background']['children'],
                'eligibilities' => $data['civil_service_eligibility'],
                'work'       => $data['work_experience'],
                'voluntary'  => $data['voluntary_work'],
                'training'   => $data['learning_development'],
                'other_info' => $data['other_information']['memberships'],
                'references' => $data['declarations']['references'],
            };

            $this->assertCount($count, $rows, "section {$key} was not padded to {$count} rows");
        }
    }

    public function test_extra_rows_beyond_the_printed_count_are_truncated(): void
    {
        $profile = $this->profile();

        foreach (range(1, PdsDataAssembler::PRINTED_ROWS['eligibilities'] + 4) as $i) {
            $profile->civilServiceEligibilities()->create([
                'eligibility_name' => "Eligibility {$i}",
                'sort_order'       => $i,
            ]);
        }

        $rows = $this->assemble($profile)['civil_service_eligibility'];

        $this->assertCount(PdsDataAssembler::PRINTED_ROWS['eligibilities'], $rows);
    }

    public function test_unanswered_questions_stay_null_rather_than_becoming_no(): void
    {
        $profile = $this->profile();
        $profile->declaration()->create(['q36_convicted' => false]);

        $data = $this->assemble($profile)['declarations'];

        $this->assertFalse($data['q36'], 'an explicit No must survive as false');
        $this->assertNull($data['q34a'], 'an unanswered question must not render as No');
    }

    public function test_other_information_is_split_into_its_three_columns(): void
    {
        $profile = $this->profile();
        $profile->otherInfos()->create(['kind' => PdsOtherInfo::KIND_SPECIAL_SKILL_HOBBY, 'value' => 'Photography']);
        $profile->otherInfos()->create(['kind' => PdsOtherInfo::KIND_MEMBERSHIP, 'value' => 'PASUC']);

        $data = $this->assemble($profile)['other_information'];

        $this->assertSame('Photography', $data['special_skills_hobbies'][0]);
        $this->assertSame('PASUC', $data['memberships'][0]);
        $this->assertSame('', $data['non_academic_distinctions'][0]);
    }

    public function test_education_is_keyed_by_level(): void
    {
        $profile = $this->profile();
        $profile->educationalAttainments()->create([
            'level' => 'college', 'school_name' => 'University of the Philippines',
            'degree_course' => 'BS Public Administration', 'year_graduated' => '2012',
        ]);

        $data = $this->assemble($profile)['educational_background'];

        $this->assertSame('University of the Philippines', $data['college']['school_name']);
        $this->assertSame('N/A', $data['elementary']['school_name']);
        $this->assertArrayHasKey('vocational_trade', $data);
        $this->assertArrayHasKey('graduate_studies', $data);
    }

    public function test_both_addresses_are_assembled_separately(): void
    {
        $profile = $this->profile(['perm_city_municipality' => 'Palo']);

        $data = $this->assemble($profile)['personal_information'];

        $this->assertSame('Tacloban City', $data['residential_address']['city_municipality']);
        $this->assertSame('Palo', $data['permanent_address']['city_municipality']);
        $this->assertSame('6500', $data['residential_address']['zip_code']);
    }
}
