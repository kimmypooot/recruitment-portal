<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\CbweRating;
use App\Models\HrmpsbComposition;
use App\Models\QsEvaluation;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * HrmpsbController::pipelineStages() was rewritten from ~17 queries per
 * requested vacancy into a handful of batched queries across all of them.
 * These tests pin its per-vacancy output (including the withdrawn-application
 * edge cases PipelineStageService documents) so that batching couldn't
 * quietly change what it reports.
 */
class PipelineStagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA ignore_check_constraints = ON');
        }

        $this->admin = User::factory()->admin()->create();
    }

    private function application(Vacancy $vacancy, string $status = 'submitted'): Application
    {
        $profile = ApplicantProfile::factory()->complete()->create();

        return Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => $status,
            'submitted_at' => now(),
        ]);
    }

    public function test_a_vacancy_with_no_applications_reports_all_flags_false(): void
    {
        $vacancy = Vacancy::factory()->published()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/hrmpsb/pipeline-stages?vacancy_ids[]='.$vacancy->id)
            ->assertOk();

        $flags = $response->json((string) $vacancy->id);
        $this->assertNotNull($flags);
        $this->assertFalse($flags['qs_exists']);
        $this->assertFalse($flags['qs_locked']);
        $this->assertFalse($flags['cbwe_exists']);
        $this->assertFalse($flags['pre_assessment_exists']);
    }

    public function test_qs_flags_still_count_a_withdrawn_applications_evaluation_but_other_flags_do_not(): void
    {
        $vacancy = Vacancy::factory()->published()->create();
        $withdrawn = $this->application($vacancy, 'withdrawn');

        QsEvaluation::create([
            'application_id'    => $withdrawn->id,
            'evaluator_id'      => $this->admin->id,
            'education_meets'   => true,
            'experience_meets'  => true,
            'training_meets'    => true,
            'eligibility_meets' => true,
            'overall_qualified' => true,
            'evaluated_at'      => now(),
            'locked_at'         => now(),
        ]);

        $flags = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/hrmpsb/pipeline-stages?vacancy_ids[]='.$vacancy->id)
            ->assertOk()
            ->json((string) $vacancy->id);

        // qs_exists/qs_locked deliberately include withdrawn applications
        // (PipelineStageService's convention).
        $this->assertTrue($flags['qs_exists']);
        $this->assertTrue($flags['qs_locked']);

        // Every other flag is scoped to non-withdrawn applications, and this
        // vacancy has none, so pre-assessment/CBWE/etc. must stay false even
        // though the vacancy is not "empty" overall.
        $this->assertFalse($flags['pre_assessment_exists']);
        $this->assertFalse($flags['cbwe_exists']);
    }

    public function test_multiple_vacancies_in_one_request_do_not_leak_state_into_each_other(): void
    {
        $vacancyWithCbwe = Vacancy::factory()->published()->create();
        $appWithCbwe = $this->application($vacancyWithCbwe);
        CbweRating::create([
            'application_id'    => $appWithCbwe->id,
            'evaluator_id'      => $this->admin->id,
            'competency_scores' => [],
            'rated_at'          => now(),
            'locked_at'         => now(),
        ]);

        $vacancyWithoutCbwe = Vacancy::factory()->published()->create();
        $this->application($vacancyWithoutCbwe);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/hrmpsb/pipeline-stages?vacancy_ids[]='.$vacancyWithCbwe->id.'&vacancy_ids[]='.$vacancyWithoutCbwe->id)
            ->assertOk();

        $withCbwe = $response->json((string) $vacancyWithCbwe->id);
        $withoutCbwe = $response->json((string) $vacancyWithoutCbwe->id);

        $this->assertTrue($withCbwe['cbwe_exists']);
        $this->assertTrue($withCbwe['cbwe_locked']);
        $this->assertFalse($withoutCbwe['cbwe_exists']);
        $this->assertFalse($withoutCbwe['cbwe_locked']);
    }
}
