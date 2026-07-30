<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicantProfile;
use App\Models\BackgroundCheck;
use App\Models\BackgroundInvestigationReport;
use App\Models\BeiRating;
use App\Models\CbweRating;
use App\Models\DeliberationResult;
use App\Models\EoptResult;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\InterviewSchedule;
use App\Models\PreAssessment;
use App\Models\QsEvaluation;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\PipelineStageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipelineStageServiceTest extends TestCase
{
    use RefreshDatabase;

    private PipelineStageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PipelineStageService();
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function createVacancy(): Vacancy
    {
        return Vacancy::factory()->create();
    }

    private function makeApplication(Vacancy $vacancy, string $status = 'submitted'): Application
    {
        $profile = ApplicantProfile::factory()->create();

        return Application::create([
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
            'status'       => $status,
            'submitted_at' => now(),
        ]);
    }

    private function adminUser(): User
    {
        return User::factory()->admin()->create();
    }

    // ── resolveFlags() — No applications ───────────────────────────────────

    public function test_resolve_flags_returns_all_false_when_vacancy_has_no_applications(): void
    {
        $vacancy = $this->createVacancy();

        $flags = $this->service->resolveFlags($vacancy);

        $expectedKeys = [
            'pre_assessment_exists',
            'qs_exists', 'qs_locked',
            'twe_scheduled', 'twe_exists',
            'cbwe_scheduled', 'cbwe_exists', 'cbwe_locked',
            'bei_scheduled', 'bei_exists', 'bei_locked',
            'eopt_exists',
            'background_check_exists', 'background_check_locked',
            'deliberation_exists',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $flags, "Flag '$key' must be present in resolveFlags() output");
            $this->assertFalse($flags[$key], "Flag '$key' should be false when vacancy has no applications");
        }
    }

    // ── resolveFlags() — Individual flag detection ────────────────────────

    public function test_resolve_flags_detects_pre_assessment_exists(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        PreAssessment::create([
            'application_id' => $application->id,
            'assessed_by'    => $this->adminUser()->id,
            'assessed_at'    => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['pre_assessment_exists']);
    }

    public function test_resolve_flags_detects_qs_locked(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        QsEvaluation::create([
            'application_id'    => $application->id,
            'evaluator_id'      => $this->adminUser()->id,
            'overall_qualified' => true,
            'locked_at'         => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['qs_locked']);
        $this->assertTrue($flags['qs_exists']);
    }

    public function test_resolve_flags_detects_twe_scheduled(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        ExamSchedule::create([
            'application_id' => $application->id,
            'exam_type'      => 'TWE',
            'scheduled_at'   => now()->addDay(),
            'venue'          => 'Exam Hall A',
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['twe_scheduled']);
    }

    public function test_resolve_flags_detects_twe_exists(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        ExamResult::create([
            'application_id' => $application->id,
            'exam_type'      => 'TWE',
            'raw_score'      => 85,
            'max_score'      => 100,
            'encoded_by'     => $this->adminUser()->id,
            'encoded_at'     => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['twe_exists']);
    }

    public function test_resolve_flags_detects_cbwe_scheduled(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        ExamSchedule::create([
            'application_id' => $application->id,
            'exam_type'      => 'CBWE',
            'scheduled_at'   => now()->addDay(),
            'venue'          => 'Exam Hall B',
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['cbwe_scheduled']);
    }

    public function test_resolve_flags_detects_cbwe_locked(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        CbweRating::create([
            'application_id'    => $application->id,
            'evaluator_id'      => $this->adminUser()->id,
            'competency_scores' => ['exemplifying_integrity' => 4],
            'total_rating'      => 4,
            'locked_at'         => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['cbwe_exists']);
        $this->assertTrue($flags['cbwe_locked']);
    }

    public function test_resolve_flags_detects_bei_scheduled(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        InterviewSchedule::create([
            'application_id' => $application->id,
            'scheduled_at'   => now()->addDay(),
            'venue'          => 'Interview Room 1',
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['bei_scheduled']);
    }

    public function test_resolve_flags_detects_bei_locked(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        BeiRating::create([
            'application_id'    => $application->id,
            'evaluator_id'      => $this->adminUser()->id,
            'competency_scores' => ['professionalism_ethics' => 4],
            'total_rating'      => 4,
            'locked_at'         => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['bei_exists']);
        $this->assertTrue($flags['bei_locked']);
    }

    public function test_resolve_flags_detects_eopt_exists(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        EoptResult::create([
            'application_id'          => $application->id,
            'emotional_stability'     => 'high',
            'extraversion'            => 'average',
            'openness_to_experience'  => 'high',
            'agreeableness'           => 'high',
            'conscientiousness'       => 'high',
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['eopt_exists']);
    }

    public function test_resolve_flags_detects_background_check_exists_via_check(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        BackgroundCheck::create([
            'application_id'      => $application->id,
            'checked_by'          => $this->adminUser()->id,
            'employment_verified' => true,
            'checked_at'          => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['background_check_exists']);
    }

    public function test_resolve_flags_detects_background_check_exists_via_investigation_report(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        BackgroundInvestigationReport::create([
            'application_id'     => $application->id,
            'investigator_name'  => 'Detective John',
            'investigator_email' => 'john@example.com',
            'token'              => 'some-token',
            'submitted_at'       => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['background_check_exists']);
    }

    public function test_resolve_flags_detects_background_check_locked(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        BackgroundCheck::create([
            'application_id'      => $application->id,
            'checked_by'          => $this->adminUser()->id,
            'employment_verified' => true,
            'checked_at'          => now(),
            'locked_at'           => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['background_check_locked']);
    }

    public function test_resolve_flags_detects_deliberation_exists(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        DeliberationResult::create([
            'vacancy_id'     => $vacancy->id,
            'application_id' => $application->id,
            'action'         => 'endorsed',
            'rank'           => 1,
            'decided_by'     => $this->adminUser()->id,
            'decided_at'     => now(),
            'locked_at'      => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['deliberation_exists']);
    }

    // ── resolveFlags() — Withdrawn application handling ───────────────────

    public function test_resolve_flags_excludes_withdrawn_applications_from_most_flags(): void
    {
        $vacancy = $this->createVacancy();
        $withdrawn = $this->makeApplication($vacancy, 'withdrawn');

        PreAssessment::create([
            'application_id' => $withdrawn->id,
            'assessed_by'    => $this->adminUser()->id,
            'assessed_at'    => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertFalse(
            $flags['pre_assessment_exists'],
            'Pre-assessment on a withdrawn application should not set pre_assessment_exists'
        );
    }

    public function test_resolve_flags_includes_withdrawn_applications_for_qs(): void
    {
        $vacancy = $this->createVacancy();
        $withdrawn = $this->makeApplication($vacancy, 'withdrawn');

        QsEvaluation::create([
            'application_id'    => $withdrawn->id,
            'evaluator_id'      => $this->adminUser()->id,
            'overall_qualified' => false,
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue(
            $flags['qs_exists'],
            'QS evaluation on a withdrawn application should still count (qs uses $allAppIds)'
        );
    }

    public function test_resolve_flags_excludes_withdrawn_but_active_applications_still_count(): void
    {
        $vacancy = $this->createVacancy();
        $this->makeApplication($vacancy, 'withdrawn');
        $active = $this->makeApplication($vacancy, 'submitted');

        PreAssessment::create([
            'application_id' => $active->id,
            'assessed_by'    => $this->adminUser()->id,
            'assessed_at'    => now(),
        ]);

        $flags = $this->service->resolveFlags($vacancy);

        $this->assertTrue($flags['pre_assessment_exists'], 'Active application must still be detected');
    }

    // ── isStageAccessible() — Prerequisite gating ─────────────────────────

    public function test_is_stage_accessible_returns_false_when_prerequisite_flag_is_missing(): void
    {
        $vacancy = $this->createVacancy();

        $this->assertFalse($this->service->isStageAccessible('qs', $vacancy));
    }

    public function test_is_stage_accessible_uses_own_data_fallback(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        QsEvaluation::create([
            'application_id'    => $application->id,
            'evaluator_id'      => $this->adminUser()->id,
            'overall_qualified' => false,
        ]);

        $this->assertTrue($this->service->isStageAccessible('qs', $vacancy));
    }

    public function test_is_stage_accessible_returns_true_when_all_prerequisites_met(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        PreAssessment::create([
            'application_id' => $application->id,
            'assessed_by'    => $this->adminUser()->id,
            'assessed_at'    => now(),
        ]);

        $this->assertTrue($this->service->isStageAccessible('qs', $vacancy));
    }

    public function test_is_stage_accessible_requires_nested_prerequisites(): void
    {
        $vacancy = $this->createVacancy();
        $application = $this->makeApplication($vacancy);

        PreAssessment::create([
            'application_id' => $application->id,
            'assessed_by'    => $this->adminUser()->id,
            'assessed_at'    => now(),
        ]);

        QsEvaluation::create([
            'application_id'    => $application->id,
            'evaluator_id'      => $this->adminUser()->id,
            'overall_qualified' => true,
            'locked_at'         => now(),
        ]);

        $this->assertTrue($this->service->isStageAccessible('twe', $vacancy));
    }

    // ── isStageAccessible() — Edge cases ──────────────────────────────────

    public function test_pre_assessment_is_always_accessible(): void
    {
        $vacancy = $this->createVacancy();

        $this->assertTrue($this->service->isStageAccessible('pre-assessment', $vacancy));
    }

    public function test_is_stage_accessible_returns_true_for_unknown_stage_key(): void
    {
        $vacancy = $this->createVacancy();

        $this->assertTrue($this->service->isStageAccessible('nonexistent-stage', $vacancy));
    }
}
