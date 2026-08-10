<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\PdsDeclaration;
use App\Models\PdsOtherInfo;
use App\Models\User;
use App\Services\Pds\PdsExcelGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;
use ZipArchive;

class PdsExcelGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private string $output;

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = storage_path('framework/testing/pds-'.uniqid().'.xlsx');
    }

    protected function tearDown(): void
    {
        if (is_file($this->output)) {
            unlink($this->output);
        }

        parent::tearDown();
    }

    private function generator(): PdsExcelGenerator
    {
        return app(PdsExcelGenerator::class);
    }

    private function profile(): ApplicantProfile
    {
        $user = User::factory()->applicant()->create([
            'first_name' => 'Juan', 'last_name' => 'Dela Cruz',
            'middle_name' => 'Reyes', 'suffix' => 'Jr.',
        ]);

        $profile = ApplicantProfile::factory()->complete()->pdsPersonalInfo()->create([
            'user_id'        => $user->id,
            'birthday'       => '1990-03-17',
            'gender'         => 'Male',
            'civil_status'   => 'Single',
            'citizenship'    => 'filipino',
            'citizenship_basis' => 'by_birth',
            'philsys_number' => '2059-8569-1428-1039',
            'blood_type'     => 'O+',
        ]);

        $profile->familyBackground()->create([
            'father_surname'           => 'Dela Cruz',
            'mother_maiden_surname'    => 'Reyes',
            'mother_maiden_first_name' => 'Maria',
        ]);
        $profile->children()->create(['full_name' => 'Ana Dela Cruz', 'date_of_birth' => '2015-04-02']);
        $profile->educationalAttainments()->create([
            'level' => 'college', 'school_name' => 'University of the Philippines',
            'degree_course' => 'BS Public Administration', 'year_graduated' => '2012',
        ]);
        $profile->civilServiceEligibilities()->create([
            'eligibility_name' => 'Career Service Professional',
            'rating' => '85.50', 'examination_date' => '2019-08-04',
        ]);
        $profile->workExperiences()->create([
            'position_title' => 'Administrative Officer', 'department_agency' => 'Civil Service Commission',
            'monthly_salary' => 21877, 'salary_grade' => '11-1', 'date_from' => '2020-01-05',
            'government_service' => true,
        ]);
        $profile->voluntaryWorks()->create([
            'organization_name_address' => 'Philippine Red Cross, Tacloban',
            'number_of_hours' => 40, 'date_from' => '2021-06-01',
        ]);
        $profile->trainings()->create([
            'title' => 'Data Privacy Act Seminar', 'date_from' => '2022-05-10',
            'hours' => 8, 'ld_type' => 'Technical', 'conducted_by' => 'Civil Service Commission',
        ]);
        $profile->otherInfos()->create(['kind' => PdsOtherInfo::KIND_MEMBERSHIP, 'value' => 'PASUC']);
        $profile->declaration()->create(array_merge(
            array_fill_keys(PdsDeclaration::REQUIRED_ANSWERS, false),
            ['q36_convicted' => true, 'q36_details' => 'Dismissed 2015', 'government_id_type' => 'PRC']
        ));
        foreach (range(1, 3) as $i) {
            $profile->references()->create(['name' => "Reference {$i}", 'sort_order' => $i]);
        }

        return $profile->fresh();
    }

    private function cell(string $sheet, string $coordinate): string
    {
        $book = IOFactory::createReader('Xlsx')->load($this->output);

        return trim((string) $book->getSheetByName($sheet)->getCell($coordinate)->getValue());
    }

    public function test_the_official_template_is_present_and_is_the_2026_revision(): void
    {
        $book  = IOFactory::createReader('Xlsx')->load($this->generator()->templatePath());
        $names = $book->getSheetNames();

        $this->assertSame(['C1', 'C2', 'C3', 'C4', 'Lookup'], $names);
        $this->assertStringContainsString(
            'Revised 2026',
            (string) $book->getSheetByName('C1')->getCell('A61')->getValue()
        );
        // The column MC 05 s. 2026 restored to Section V.
        $this->assertStringContainsString(
            'STEP INCREMENT',
            (string) $book->getSheetByName('C2')->getCell('K15')->getValue()
        );
    }

    public function test_it_writes_personal_information_into_the_mapped_cells(): void
    {
        $this->generator()->generate($this->profile(), $this->output);

        $this->assertSame('Dela Cruz', $this->cell('C1', 'D10'));
        $this->assertSame('Juan', $this->cell('C1', 'D11'));
        $this->assertSame('Reyes', $this->cell('C1', 'D12'));
        $this->assertSame('17/03/1990', $this->cell('C1', 'D13'));
        $this->assertSame('Tacloban City', $this->cell('C1', 'D15'));
        $this->assertSame('2059-8569-1428-1039', $this->cell('C1', 'D32'));
    }

    public function test_it_writes_both_addresses(): void
    {
        $this->generator()->generate($this->profile(), $this->output);

        $this->assertSame('Tacloban City', $this->cell('C1', 'I22'));  // residential city
        $this->assertSame('Leyte', $this->cell('C1', 'L22'));          // residential province
        $this->assertSame('Tacloban City', $this->cell('C1', 'I29'));  // permanent city
        $this->assertSame('6500', $this->cell('C1', 'I31'));           // permanent zip
    }

    public function test_it_writes_the_repeating_sections_at_their_first_rows(): void
    {
        $this->generator()->generate($this->profile(), $this->output);

        $this->assertSame('Ana Dela Cruz', $this->cell('C1', 'I37'));
        $this->assertSame('02/04/2015', $this->cell('C1', 'M37'));

        $this->assertSame('Career Service Professional', $this->cell('C2', 'A5'));
        $this->assertSame('04/08/2019', $this->cell('C2', 'G5'));

        $this->assertSame('Administrative Officer', $this->cell('C2', 'D18'));
        $this->assertSame('21,877.00', $this->cell('C2', 'J18'));
        $this->assertSame('11-1', $this->cell('C2', 'K18'));
        $this->assertSame('Y', $this->cell('C2', 'M18'));

        $this->assertSame('Philippine Red Cross, Tacloban', $this->cell('C3', 'A6'));
        $this->assertSame('Data Privacy Act Seminar', $this->cell('C3', 'A18'));
        $this->assertSame('PASUC', $this->cell('C3', 'I42'));
        $this->assertSame('Reference 1', $this->cell('C4', 'A52'));
    }

    public function test_education_is_written_to_the_row_for_its_level(): void
    {
        $this->generator()->generate($this->profile(), $this->output);

        // College is row 57; elementary (row 54) has no entry so falls back to N/A.
        $this->assertSame('University of the Philippines', $this->cell('C1', 'D57'));
        $this->assertSame('2012', $this->cell('C1', 'M57'));
        $this->assertSame('N/A', $this->cell('C1', 'D54'));
    }

    public function test_details_are_appended_to_their_printed_label(): void
    {
        $this->generator()->generate($this->profile(), $this->output);

        $q36 = $this->cell('C4', 'G24');

        $this->assertStringStartsWith('If YES, give details:', $q36);
        $this->assertStringEndsWith('Dismissed 2015', $q36);
    }

    public function test_the_template_itself_is_never_modified(): void
    {
        $template = $this->generator()->templatePath();
        $before   = md5_file($template);

        $this->generator()->generate($this->profile(), $this->output);

        $this->assertSame($before, md5_file($template));
    }

    public function test_generation_preserves_the_layout_that_makes_the_form_compliant(): void
    {
        $this->generator()->generate($this->profile(), $this->output);

        $book = IOFactory::createReader('Xlsx')->load($this->output);

        $expectedMerges = ['C1' => 140, 'C2' => 127, 'C3' => 83, 'C4' => 85];
        foreach ($expectedMerges as $sheet => $count) {
            $this->assertCount($count, $book->getSheetByName($sheet)->getMergeCells(), "merges lost on {$sheet}");
        }

        $this->assertSame('A1:N61', $book->getSheetByName('C1')->getPageSetup()->getPrintArea());
        $this->assertSame('A1:M71', $book->getSheetByName('C4')->getPageSetup()->getPrintArea());
    }

    public function test_form_controls_survive_generation(): void
    {
        $this->generator()->generate($this->profile(), $this->output);

        $zip = new ZipArchive;
        $zip->open($this->output);

        $ctrlProps = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (str_contains((string) $zip->getNameIndex($i), 'ctrlProps/')) {
                $ctrlProps++;
            }
        }
        $zip->close();

        $this->assertSame(37, $ctrlProps, 'the template\'s form controls were dropped');
    }

    public function test_answered_questions_tick_a_box_and_unanswered_ones_tick_neither(): void
    {
        $profile = $this->profile();
        $profile->declaration->update([
            'q36_convicted'             => true,   // YES
            'q37_separated_from_service' => false, // NO
            'q39_immigrant_status'      => null,   // unanswered
        ]);

        $this->generator()->generate($profile->fresh(), $this->output);

        $map = config('pds_checkbox_map');
        $vml = $this->vmlFor('C4');

        $this->assertTrue($this->isChecked($vml, $map['declarations.q36']['yes']));
        $this->assertFalse($this->isChecked($vml, $map['declarations.q36']['no']));

        $this->assertTrue($this->isChecked($vml, $map['declarations.q37']['no']));
        $this->assertFalse($this->isChecked($vml, $map['declarations.q37']['yes']));

        $this->assertFalse($this->isChecked($vml, $map['declarations.q39']['yes']));
        $this->assertFalse(
            $this->isChecked($vml, $map['declarations.q39']['no']),
            'an unanswered question must not be rendered as NO'
        );
    }

    public function test_option_group_checkboxes_are_ticked(): void
    {
        $this->generator()->generate($this->profile(), $this->output);

        $map = config('pds_checkbox_map');
        $vml = $this->vmlFor('C1');

        $this->assertTrue($this->isChecked($vml, $map['personal_information.sex_at_birth']['options']['Male']['anchor']));
        $this->assertFalse($this->isChecked($vml, $map['personal_information.sex_at_birth']['options']['Female']['anchor']));
        $this->assertTrue($this->isChecked($vml, $map['personal_information.civil_status']['options']['Single']['anchor']));
        $this->assertTrue($this->isChecked($vml, $map['personal_information.citizenship']['options']['filipino']['anchor']));
    }

    /**
     * The checkbox map is only correct while its anchors and captions match the
     * template. If CSC reissues the workbook, this is the test that catches it.
     */
    public function test_every_mapped_checkbox_exists_in_the_template_with_the_expected_caption(): void
    {
        $zip = new ZipArchive;
        $zip->open($this->generator()->templatePath());

        $shapes = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if (! str_contains($name, 'vmlDrawing')) {
                continue;
            }
            preg_match_all('/<v:shape\b.*?<\/v:shape>/s', (string) $zip->getFromName($name), $found);
            foreach ($found[0] as $shape) {
                if (preg_match('/<x:Anchor>\s*([^<]+)<\/x:Anchor>/s', $shape, $anchor)) {
                    $shapes[preg_replace('/\s+/', '', $anchor[1])] = trim(preg_replace('/\s+/', ' ', strip_tags($shape)));
                }
            }
        }
        $zip->close();

        foreach (config('pds_checkbox_map') as $path => $entry) {
            if (isset($entry['yes'])) {
                $this->assertArrayHasKey($entry['yes'], $shapes, "{$path} YES anchor missing");
                $this->assertArrayHasKey($entry['no'], $shapes, "{$path} NO anchor missing");
                $this->assertStringContainsString('YES', $shapes[$entry['yes']], "{$path} YES caption mismatch");
                $this->assertStringContainsString('NO', $shapes[$entry['no']], "{$path} NO caption mismatch");

                continue;
            }

            foreach ($entry['options'] as $option => $definition) {
                $this->assertArrayHasKey($definition['anchor'], $shapes, "{$path}.{$option} anchor missing");
                $this->assertStringContainsString(
                    $definition['caption'],
                    $shapes[$definition['anchor']],
                    "{$path}.{$option} caption mismatch"
                );
            }
        }
    }

    private function vmlFor(string $sheet): string
    {
        // C1 is the first sheet, C4 the fourth; their legacy drawings are the
        // only VML parts carrying form controls.
        $zip = new ZipArchive;
        $zip->open($this->output);

        $candidates = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if (str_contains($name, 'vmlDrawing') && str_ends_with($name, '.vml')) {
                $candidates[$name] = (string) $zip->getFromName($name);
            }
        }
        $zip->close();

        // Sheet C1's controls include the "Filipino" caption; C4's are YES/NO.
        foreach ($candidates as $content) {
            $hasFilipino = str_contains($content, 'Filipino');
            if (($sheet === 'C1' && $hasFilipino) || ($sheet === 'C4' && ! $hasFilipino && str_contains($content, 'YES'))) {
                return $content;
            }
        }

        $this->fail("No VML drawing found for sheet {$sheet}");
    }

    private function isChecked(string $vml, string $anchor): bool
    {
        preg_match_all('/<v:shape\b.*?<\/v:shape>/s', $vml, $shapes);

        foreach ($shapes[0] as $shape) {
            if (! preg_match('/<x:Anchor>\s*([^<]+)<\/x:Anchor>/s', $shape, $found)) {
                continue;
            }

            if (preg_replace('/\s+/', '', $found[1]) === $anchor) {
                return str_contains($shape, '<x:Checked>1</x:Checked>');
            }
        }

        $this->fail("Anchor {$anchor} not found in output");
    }
}
