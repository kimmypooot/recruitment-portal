<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\User;
use App\Services\Pds\PdsPdfGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Tests\TestCase;

class PdsPdfGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private string $output;

    protected function setUp(): void
    {
        parent::setUp();
        $this->output = storage_path('framework/testing/pds-'.uniqid().'.pdf');
    }

    protected function tearDown(): void
    {
        if (is_file($this->output)) {
            unlink($this->output);
        }

        parent::tearDown();
    }

    private function generator(): PdsPdfGenerator
    {
        return app(PdsPdfGenerator::class);
    }

    private function profile(): ApplicantProfile
    {
        $user = User::factory()->applicant()->create(['first_name' => 'Juan', 'last_name' => 'Dela Cruz']);

        return ApplicantProfile::factory()->complete()->pdsPersonalInfo()->create([
            'user_id'  => $user->id,
            'birthday' => '1990-03-17',
            'gender'   => 'Male',
        ]);
    }

    public function test_it_reports_libreoffice_as_unavailable_when_the_configured_path_is_wrong(): void
    {
        Config::set('pds.soffice_path', '/definitely/not/a/real/soffice');

        $this->assertFalse($this->generator()->isAvailable());
    }

    public function test_it_fails_with_an_actionable_message_when_libreoffice_is_missing(): void
    {
        Config::set('pds.soffice_path', '/definitely/not/a/real/soffice');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/LibreOffice was not found.*PDS_SOFFICE_PATH/s');

        $this->generator()->generate($this->profile(), $this->output);
    }

    public function test_a_failed_conversion_leaves_no_temporary_files_behind(): void
    {
        Config::set('pds.soffice_path', '/definitely/not/a/real/soffice');

        $before = $this->temporaryFileCount();

        try {
            $this->generator()->generate($this->profile(), $this->output);
        } catch (RuntimeException) {
            // expected
        }

        $this->assertSame($before, $this->temporaryFileCount(), 'temporary workbook was not cleaned up');
    }

    public function test_it_renders_the_form_to_a_four_page_pdf(): void
    {
        $generator = $this->generator();

        if (! $generator->isAvailable()) {
            $this->markTestSkipped(
                'LibreOffice is not installed on this host, so PDF rendering cannot be verified. '
                .'Install it or set PDS_SOFFICE_PATH to run this test.'
            );
        }

        $generator->generate($this->profile(), $this->output);

        $this->assertFileExists($this->output);

        $raw = (string) file_get_contents($this->output);
        $this->assertStringStartsWith('%PDF', $raw);

        preg_match_all('#/Type\s*/Page[^s]#', $raw, $pages);
        $this->assertCount(
            config('pds.expected_pages'),
            $pages[0],
            'the PDS must render as exactly four pages'
        );
    }

    private function temporaryFileCount(): int
    {
        $directory = storage_path('app/pds-tmp');

        if (! is_dir($directory)) {
            return 0;
        }

        return count(scandir($directory)) - 2;
    }
}
