<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\ApplicationNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationNumberTest extends TestCase
{
    use RefreshDatabase;

    private function vacancy(string $title, ?string $plantilla, string $publishedAt = '2026-11-05'): Vacancy
    {
        return Vacancy::factory()->published()->create([
            'position_title' => $title,
            'plantilla_no' => $plantilla,
            'published_at' => $publishedAt,
        ]);
    }

    private function apply(User $user, Vacancy $vacancy, bool $withReply = false)
    {
        $data = ['vacancy_id' => $vacancy->id];
        if ($withReply) {
            $data['interest_reply'] = UploadedFile::fake()->create('reply.pdf', 50, 'application/pdf');
        }

        return $this->actingAs($user, 'sanctum')->post('/api/applications', $data, ['Accept' => 'application/json']);
    }

    private function applicant(array $attributes = []): User
    {
        $user = User::factory()->applicant()->create($attributes);
        ApplicantProfile::factory()->complete()->withDocuments()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_the_position_code_is_the_initials_with_the_level_kept_whole(): void
    {
        $service = new ApplicationNumber;

        $this->assertSame('AOII', $service->positionCode('Administrative Officer II'));
        $this->assertSame('HRMOIII', $service->positionCode('Human Resource Management Officer III'));
        $this->assertSame('SHRS', $service->positionCode('Senior Human Resource Specialist'));
        $this->assertSame('DCS', $service->positionCode('Director of the Central Services'));
        $this->assertSame('POS', $service->positionCode('   '));
    }

    public function test_the_number_is_position_item_month_year_and_a_running_applicant_number(): void
    {
        $vacancy = $this->vacancy('Administrative Officer II', 'ADOF2-53-2005');

        $first = $this->apply($this->applicant(), $vacancy)->assertCreated()->json('reference_code');
        $second = $this->apply($this->applicant(), $vacancy)->assertCreated()->json('reference_code');

        $this->assertSame('AOII-53-11-2026-0001', $first);
        $this->assertSame('AOII-53-11-2026-0002', $second);
    }

    public function test_two_vacancies_for_the_same_position_in_the_same_month_get_different_numbers(): void
    {
        $one = $this->vacancy('Administrative Officer II', 'ADOF2-53-2005');
        $two = $this->vacancy('Administrative Officer II', 'ADOF2-54-2005');

        $a = $this->apply($this->applicant(), $one)->assertCreated()->json('reference_code');
        $b = $this->apply($this->applicant(), $two)->assertCreated()->json('reference_code');

        $this->assertSame('AOII-53-11-2026-0001', $a);
        $this->assertSame('AOII-54-11-2026-0001', $b);
        $this->assertNotSame($a, $b);
    }

    public function test_the_same_prefix_never_repeats_even_if_two_vacancies_share_it(): void
    {
        // Same position, same item serial, same month: the count carries on across them.
        $one = $this->vacancy('Administrative Officer II', 'ADOF2-53-2005');
        $two = $this->vacancy('Administrative Officer II', 'ADOF2-53-2010');

        $a = $this->apply($this->applicant(), $one)->assertCreated()->json('reference_code');
        $b = $this->apply($this->applicant(), $two)->assertCreated()->json('reference_code');

        $this->assertSame('AOII-53-11-2026-0001', $a);
        $this->assertSame('AOII-53-11-2026-0002', $b);
    }

    public function test_the_month_is_the_vacancys_publication_month_and_a_withdrawn_number_is_not_reused(): void
    {
        $vacancy = $this->vacancy('Accountant III', 'A3-53-2005', '2026-12-03');
        $first = $this->applicant();

        $a = $this->apply($first, $vacancy)->assertCreated()->json('reference_code');
        Application::where('reference_code', $a)->firstOrFail()->delete();
        $b = $this->apply($this->applicant(), $vacancy)->assertCreated()->json('reference_code');

        $this->assertSame('AIII-53-12-2026-0001', $a);
        $this->assertSame('AIII-53-12-2026-0002', $b);
    }

    public function test_a_vacancy_without_a_plantilla_item_uses_its_own_id(): void
    {
        $vacancy = $this->vacancy('Attorney IV', null);

        $number = $this->apply($this->applicant(), $vacancy)->assertCreated()->json('reference_code');

        $this->assertSame("AIV-V{$vacancy->id}-11-2026-0001", $number);
    }

    public function test_an_employee_must_attach_a_signed_reply_and_it_is_stored_with_the_application(): void
    {
        Storage::fake('local');
        $employee = $this->applicant(['imis_user_id' => 9]);
        $vacancy = $this->vacancy('Administrative Officer II', 'ADOF2-53-2005');

        $this->apply($employee, $vacancy)->assertUnprocessable();
        $this->assertDatabaseCount('applications', 0);

        $response = $this->apply($employee, $vacancy, withReply: true)->assertCreated();

        $application = Application::findOrFail($response->json('id'));
        $document = $application->documents()->where('document_type', 'interest_reply')->firstOrFail();
        Storage::disk('local')->assertExists($document->file_path);
        $this->assertSame('reply.pdf', $document->original_filename);
    }

    public function test_the_board_and_hr_can_open_an_employees_reply_and_external_applicants_have_none(): void
    {
        Storage::fake('local');
        $vacancy = $this->vacancy('Administrative Officer II', 'ADOF2-53-2005');
        $employee = $this->applicant(['imis_user_id' => 9]);
        $outsider = $this->applicant();
        $internalId = $this->apply($employee, $vacancy, withReply: true)->assertCreated()->json('id');
        $externalId = $this->apply($outsider, $vacancy)->assertCreated()->json('id');
        $admin = User::factory()->admin()->create();

        $rows = collect($this->actingAs($admin, 'sanctum')->getJson("/api/hrmpsb/vacancies/{$vacancy->id}/applicants")->assertOk()->json('applicants'))->keyBy('id');

        $this->assertTrue($rows[$internalId]['is_internal']);
        $this->assertSame("/api/hrmpsb/applications/{$internalId}/documents/interest_reply", $rows[$internalId]['document_links']['interest_reply']);
        $this->assertFalse($rows[$externalId]['is_internal']);
        $this->assertArrayNotHasKey('interest_reply', $rows[$externalId]['document_links']);

        $this->actingAs($admin, 'sanctum')->get($rows[$internalId]['document_links']['interest_reply'])->assertOk();
        $this->actingAs($admin, 'sanctum')->get("/api/hrmpsb/applications/{$externalId}/documents/interest_reply")->assertNotFound();
    }

    public function test_an_external_applicant_needs_no_reply_and_a_reply_they_send_is_not_kept(): void
    {
        Storage::fake('local');
        $vacancy = $this->vacancy('Administrative Officer II', 'ADOF2-53-2005');

        $this->apply($this->applicant(), $vacancy)->assertCreated();
        $second = $this->apply($this->applicant(), $vacancy, withReply: true)->assertCreated();

        $this->assertSame(0, Application::findOrFail($second->json('id'))->documents()->count());
    }
}
