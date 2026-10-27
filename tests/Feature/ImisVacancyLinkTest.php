<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ReportSelectionToImis;
use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Vacancies are opened on IMIS's vacant plantilla items (read-only, server to
 * server), may still be entered by hand (e.g. Contract of Service), and the
 * Appointing Authority's selection is written back to IMIS.
 */
class ImisVacancyLinkTest extends TestCase
{
    use RefreshDatabase;

    private int $imisStatus = 200;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.imis.sso_url' => 'https://imis.test/api/v1/sso/validate',
            'services.imis.api_token' => 'secret',
        ]);

        // Registered once; the status is read when the request is made.
        Http::fake(['imis.test/api/v1/recruitment/vacant-positions' => fn () => Http::response(['data' => [
            ['id' => 11, 'item_number' => 'OSEC-CSC8-1', 'position_title' => 'Administrative Officer IV', 'salary_grade' => 15, 'monthly_salary' => 41000.5, 'level' => 'second', 'office_code' => 'HRD', 'office_name' => 'Human Resource Division'],
            ['id' => 12, 'item_number' => 'OSEC-CSC8-2', 'position_title' => 'Clerk', 'salary_grade' => 4, 'monthly_salary' => 15000, 'level' => 'first', 'office_code' => 'NOPE', 'office_name' => 'Elsewhere'],
        ]], $this->imisStatus)]);

        Sanctum::actingAs(User::factory()->admin()->create());
    }

    /** @return array<string, mixed> */
    private function qs(array $extra = []): array
    {
        return $extra + [
            'position_level' => 'Technical or Non-Supervisory',
            'education_req' => 'Bachelor', 'experience_req' => '1 year', 'training_req' => '4 hours', 'eligibility_req' => 'CSE-PPT',
            'deadline_at' => now()->addDays(20)->toDateString(),
        ];
    }

    public function test_the_picker_lists_imis_positions_with_a_recruiting_office_and_not_those_already_opened(): void
    {
        Vacancy::factory()->create(['imis_plantilla_item_id' => 99]);

        $this->getJson('/api/admin/imis-vacant-positions')->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 11)
            ->assertJsonPath('data.0.place_of_assignment', 'Human Resource Division (HRD)');

        Vacancy::factory()->create(['imis_plantilla_item_id' => 11]);
        $this->getJson('/api/admin/imis-vacant-positions')->assertJsonCount(0, 'data');
    }

    public function test_a_vacancy_takes_its_position_fields_from_imis_not_the_request(): void
    {
        $this->postJson('/api/vacancies', $this->qs([
            'imis_plantilla_item_id' => 11,
            'position_title' => 'Forged title', 'salary_grade' => 33, 'plantilla_no' => 'FORGED', 'place_of_assignment' => 'CSC Field Office - Samar',
        ]))->assertCreated();

        $this->assertDatabaseHas('vacancies', [
            'imis_plantilla_item_id' => 11, 'position_title' => 'Administrative Officer IV', 'plantilla_no' => 'OSEC-CSC8-1',
            'salary_grade' => 15, 'place_of_assignment' => 'Human Resource Division (HRD)', 'employment_type' => 'plantilla',
        ]);
    }

    public function test_an_item_that_is_not_vacant_in_imis_is_refused_and_one_item_gets_one_vacancy(): void
    {
        $this->postJson('/api/vacancies', $this->qs(['imis_plantilla_item_id' => 500]))->assertStatus(422);
        $this->postJson('/api/vacancies', $this->qs(['imis_plantilla_item_id' => 12]))->assertStatus(422);

        $this->postJson('/api/vacancies', $this->qs(['imis_plantilla_item_id' => 11]))->assertCreated();
        $this->postJson('/api/vacancies', $this->qs(['imis_plantilla_item_id' => 11]))->assertUnprocessable()->assertJsonValidationErrors('imis_plantilla_item_id');
    }

    public function test_an_unreachable_imis_does_not_create_a_linked_vacancy(): void
    {
        $this->imisStatus = 500;

        $this->postJson('/api/vacancies', $this->qs(['imis_plantilla_item_id' => 11]))->assertStatus(502);
        $this->assertDatabaseCount('vacancies', 0);
    }

    public function test_a_contract_of_service_vacancy_is_entered_by_hand_without_an_item_or_grade(): void
    {
        $this->postJson('/api/vacancies', $this->qs([
            'employment_type' => 'contract_of_service', 'position_title' => 'Administrative Aide (COS)',
            'place_of_assignment' => 'CSC Field Office - Samar', 'monthly_salary' => 18000,
        ]))->assertCreated();

        $this->assertDatabaseHas('vacancies', ['position_title' => 'Administrative Aide (COS)', 'employment_type' => 'contract_of_service', 'imis_plantilla_item_id' => null, 'salary_grade' => null]);
    }

    public function test_a_manual_plantilla_vacancy_still_needs_an_item_number_and_grade(): void
    {
        $this->postJson('/api/vacancies', $this->qs(['position_title' => 'Clerk', 'place_of_assignment' => 'CSC Field Office - Samar']))
            ->assertUnprocessable()->assertJsonValidationErrors(['plantilla_no', 'salary_grade']);
    }

    public function test_imis_owned_fields_cannot_be_edited_on_a_linked_vacancy(): void
    {
        $vacancy = Vacancy::factory()->create(['imis_plantilla_item_id' => 11, 'position_title' => 'Administrative Officer IV', 'salary_grade' => 15]);

        $this->putJson("/api/vacancies/{$vacancy->id}", ['position_title' => 'Changed', 'salary_grade' => 20, 'education_req' => 'Master'])->assertOk();

        $vacancy->refresh();
        $this->assertSame('Administrative Officer IV', $vacancy->position_title);
        $this->assertSame(15, $vacancy->salary_grade);
        $this->assertSame('Master', $vacancy->education_req);
    }

    public function test_the_appointed_selection_is_written_back_to_imis(): void
    {
        $vacancy = Vacancy::factory()->create(['imis_plantilla_item_id' => 11]);
        $application = Application::create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => ApplicantProfile::factory()->complete()->create()->id,
            'status' => 'submitted', 'submitted_at' => now(),
        ]);
        $name = $application->applicant->user->full_name;

        Http::fake(['imis.test/api/v1/recruitment/items/11/selection' => Http::response(['id' => 1], 201)]);

        (new ReportSelectionToImis($vacancy->id, $application->id))->handle(app(\App\Services\ImisPlantilla::class));

        Http::assertSent(fn ($request) => $request->url() === 'https://imis.test/api/v1/recruitment/items/11/selection'
            && $request->hasHeader('Authorization', 'Bearer secret')
            && $request['recruitment_vacancy_id'] === $vacancy->id
            && $request['applicant_name'] === $name);
    }

    public function test_a_refusal_from_imis_fails_the_job_so_it_retries(): void
    {
        $vacancy = Vacancy::factory()->create(['imis_plantilla_item_id' => 11]);
        $application = Application::create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => ApplicantProfile::factory()->complete()->create()->id,
            'status' => 'submitted', 'submitted_at' => now(),
        ]);

        Http::fake(['imis.test/*' => Http::response([], 503)]);

        $this->expectException(\RuntimeException::class);
        (new ReportSelectionToImis($vacancy->id, $application->id))->handle(app(\App\Services\ImisPlantilla::class));
    }

    public function test_a_manual_vacancy_is_never_reported(): void
    {
        $vacancy = Vacancy::factory()->create(['imis_plantilla_item_id' => null]);
        $application = Application::create([
            'vacancy_id' => $vacancy->id,
            'applicant_id' => ApplicantProfile::factory()->complete()->create()->id,
            'status' => 'submitted', 'submitted_at' => now(),
        ]);

        Http::fake();
        (new ReportSelectionToImis($vacancy->id, $application->id))->handle(app(\App\Services\ImisPlantilla::class));

        Http::assertNothingSent();
    }

    public function test_deciding_appointed_queues_the_report_only_for_linked_vacancies(): void
    {
        Queue::fake();
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        $linked = Vacancy::factory()->published()->create(['imis_plantilla_item_id' => 11]);
        $manual = Vacancy::factory()->published()->create();

        foreach ([$linked, $manual] as $vacancy) {
            $application = Application::create([
                'vacancy_id' => $vacancy->id,
                'applicant_id' => ApplicantProfile::factory()->complete()->create()->id,
                'status' => 'submitted', 'submitted_at' => now(),
            ]);

            $this->patchJson("/api/deliberation/{$vacancy->id}/appointing-authority/decide", [
                'application_id' => $application->id, 'action' => 'appointed',
            ])->assertOk();
        }

        Queue::assertPushed(ReportSelectionToImis::class, 1);
        Queue::assertPushed(ReportSelectionToImis::class, fn ($job) => $job->vacancyId === $linked->id);
    }
}
