<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\User;
use App\Services\Pds\WorkExperienceSheetGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class WorkExperienceSheetGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private string $output;

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = storage_path('framework/testing/wes-'.uniqid().'.docx');
    }

    protected function tearDown(): void
    {
        if (is_file($this->output)) {
            unlink($this->output);
        }

        parent::tearDown();
    }

    private function generator(): WorkExperienceSheetGenerator
    {
        return app(WorkExperienceSheetGenerator::class);
    }

    private function profileWithHistory(): ApplicantProfile
    {
        $profile = ApplicantProfile::factory()->complete()->create([
            'user_id' => User::factory()->applicant()->create(['last_name' => 'Dela Cruz'])->id,
        ]);

        $profile->workExperiences()->create([
            'position_title'       => 'Administrative Officer III',
            'department_agency'    => 'Civil Service Commission',
            'agency_location'      => 'Palo, Leyte',
            'office_unit'          => 'Finance and Administrative Division',
            'immediate_supervisor' => 'Maria Estrada',
            'duties'               => 'Responsible for recruitment and selection.',
            'accomplishments'      => "Developed recruitment plan\nDesigned training program",
            'date_from'            => '2021-02-11',
            'is_present'           => true,
        ]);

        $profile->workExperiences()->create([
            'position_title'    => 'Administrative Officer I',
            'department_agency' => 'Department of Finance',
            'date_from'         => '2018-01-02',
            'date_to'           => '2021-02-10',
        ]);

        return $profile->fresh();
    }

    private function documentText(): string
    {
        $zip = new ZipArchive;
        $zip->open($this->output);
        $xml = (string) $zip->getFromName('word/document.xml');
        $zip->close();

        preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $xml, $matches);

        return html_entity_decode(preg_replace('/\s+/', ' ', implode(' ', $matches[1])) ?? '');
    }

    public function test_it_refuses_when_there_is_no_work_history(): void
    {
        $profile = ApplicantProfile::factory()->complete()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/at least one work experience/i');

        $this->generator()->generate($profile, $this->output);
    }

    public function test_it_keeps_the_official_heading_and_signature_block(): void
    {
        $this->generator()->generate($this->profileWithHistory(), $this->output);

        $text = $this->documentText();

        $this->assertStringContainsString('WORK EXPERIENCE SHEET', $text);
        $this->assertStringContainsString('Attachment to CS Form No. 212', $text);
        $this->assertStringContainsString('Signature over Printed Name', $text);
    }

    public function test_the_worked_sample_is_replaced_by_the_applicants_own_entries(): void
    {
        $this->generator()->generate($this->profileWithHistory(), $this->output);

        $text = $this->documentText();

        $this->assertStringContainsString('Administrative Officer III', $text);
        $this->assertStringContainsString('Finance and Administrative Division', $text);
        $this->assertStringContainsString('Civil Service Commission, Palo, Leyte', $text);

        // The template ships a sample that must not survive into a real sheet.
        $this->assertStringNotContainsString('Sample: If applying to', $text);
        $this->assertStringNotContainsString('Department of Human Resources', $text);
        $this->assertStringNotContainsString('Celia Romano', $text);
    }

    public function test_a_current_position_is_written_as_present_and_dates_are_spelled_out(): void
    {
        $this->generator()->generate($this->profileWithHistory(), $this->output);

        $text = $this->documentText();

        // "month in abbreviated form … and year in full", per the instructions.
        $this->assertStringContainsString('February 11, 2021 – Present', $text);
        $this->assertStringContainsString('January 2, 2018 – February 10, 2021', $text);
    }

    public function test_accomplishments_are_split_one_per_line(): void
    {
        $this->generator()->generate($this->profileWithHistory(), $this->output);

        $text = $this->documentText();

        $this->assertStringContainsString('Developed recruitment plan', $text);
        $this->assertStringContainsString('Designed training program', $text);
    }

    /** The sheet is prose; "N/A" would read badly in a narrative. */
    public function test_missing_optional_detail_is_left_blank_rather_than_not_applicable(): void
    {
        $this->generator()->generate($this->profileWithHistory(), $this->output);

        $text = $this->documentText();

        // The second entry has no supervisor or office recorded.
        $this->assertStringContainsString('Immediate Supervisor:', $text);
        $this->assertStringNotContainsString('Immediate Supervisor: N/A', $text);
    }

    public function test_the_template_itself_is_never_modified(): void
    {
        $template = $this->generator()->templatePath();
        $before   = md5_file($template);

        $this->generator()->generate($this->profileWithHistory(), $this->output);

        $this->assertSame($before, md5_file($template));
    }

    public function test_an_applicant_can_download_the_sheet(): void
    {
        $profile = $this->profileWithHistory();

        $response = $this->actingAs($profile->user, 'sanctum')
            ->get('/api/profile/pds/work-experience-sheet?format=docx');

        $response->assertOk();
        $this->assertStringContainsString('.docx', $response->headers->get('content-disposition') ?? '');
    }

    public function test_an_unsupported_format_is_rejected(): void
    {
        $profile = $this->profileWithHistory();

        $this->actingAs($profile->user, 'sanctum')
            ->getJson('/api/profile/pds/work-experience-sheet?format=xlsx')
            ->assertStatus(422);
    }
}
