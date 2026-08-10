<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\PdsDeclaration;
use App\Models\User;
use App\Services\Pds\PdsCompletenessChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdsCompletenessCheckerTest extends TestCase
{
    use RefreshDatabase;

    private function checker(): PdsCompletenessChecker
    {
        return app(PdsCompletenessChecker::class);
    }

    /** A profile that satisfies every blocking section. */
    private function readyProfile(): ApplicantProfile
    {
        $user    = User::factory()->applicant()->create();
        $profile = ApplicantProfile::factory()->complete()->pdsPersonalInfo()->create(['user_id' => $user->id]);

        $profile->familyBackground()->create([
            'father_surname'        => 'Dela Cruz',
            'father_first_name'     => 'Pedro',
            'mother_maiden_surname' => 'Reyes',
            'mother_maiden_first_name' => 'Maria',
        ]);

        $profile->educationalAttainments()->create(['level' => 'elementary', 'school_name' => 'Tacloban Elem.']);
        $profile->educationalAttainments()->create(['level' => 'secondary', 'school_name' => 'Tacloban High']);

        $profile->declaration()->create(
            array_fill_keys(PdsDeclaration::REQUIRED_ANSWERS, false)
        );

        foreach (range(1, 3) as $i) {
            $profile->references()->create(['name' => "Reference {$i}", 'sort_order' => $i]);
        }

        return $profile->fresh();
    }

    public function test_an_empty_profile_cannot_generate_and_reports_every_blocking_section(): void
    {
        $profile = ApplicantProfile::factory()->create();

        $result = $this->checker()->check($profile);

        $this->assertFalse($result['can_generate']);
        $this->assertEqualsCanonicalizing(
            PdsCompletenessChecker::REQUIRED_SECTIONS,
            $result['blocking']
        );
    }

    public function test_a_fully_populated_profile_can_generate(): void
    {
        $result = $this->checker()->check($this->readyProfile());

        $this->assertTrue($result['can_generate'], 'blocking: '.implode(', ', $result['blocking']));
        $this->assertSame([], $result['blocking']);
    }

    public function test_optional_sections_never_block_generation(): void
    {
        $result = $this->checker()->check($this->readyProfile());

        foreach (['civil_service_eligibility', 'work_experience', 'voluntary_work', 'learning_development', 'other_information'] as $key) {
            $this->assertTrue($result['sections'][$key]['optional'], "{$key} should be optional");
            $this->assertFalse($result['sections'][$key]['complete'], "{$key} is empty in this fixture");
        }

        $this->assertTrue($result['can_generate']);
    }

    public function test_a_partially_answered_declaration_blocks_generation(): void
    {
        $profile = $this->readyProfile();
        $profile->declaration->update(['q37_separated_from_service' => null]);

        $result = $this->checker()->check($profile->fresh());

        $this->assertFalse($result['can_generate']);
        $this->assertContains('declarations', $result['blocking']);
        $this->assertSame(['q37_separated_from_service'], $result['sections']['declarations']['missing']);
    }

    public function test_an_answer_of_no_counts_as_answered(): void
    {
        $result = $this->checker()->check($this->readyProfile());

        $this->assertTrue($result['sections']['declarations']['complete']);
    }

    public function test_fewer_than_three_references_blocks_generation(): void
    {
        $profile = $this->readyProfile();
        $profile->references()->latest('id')->first()->delete();

        $result = $this->checker()->check($profile->fresh());

        $this->assertFalse($result['can_generate']);
        $this->assertContains('references', $result['blocking']);
        $this->assertSame(['need_1_more'], $result['sections']['references']['missing']);
    }

    public function test_missing_elementary_or_secondary_blocks_generation(): void
    {
        $profile = $this->readyProfile();
        $profile->educationalAttainments()->where('level', 'secondary')->delete();

        $result = $this->checker()->check($profile->fresh());

        $this->assertFalse($result['can_generate']);
        $this->assertSame(['secondary'], $result['sections']['educational_background']['missing']);
    }

    public function test_a_missing_permanent_address_blocks_generation(): void
    {
        $profile = $this->readyProfile();
        $profile->update([
            'perm_barangay'          => null,
            'perm_city_municipality' => null,
            'perm_province'          => null,
        ]);

        $result = $this->checker()->check($profile->fresh());

        $this->assertFalse($result['can_generate']);
        $this->assertContains('permanent_address', $result['sections']['personal_information']['missing']);
    }

    public function test_it_is_stricter_than_the_application_level_profile_check(): void
    {
        // A profile good enough to submit an application is not automatically
        // good enough to produce a PDS.
        $profile = ApplicantProfile::factory()->complete()->withDocuments()->create();

        $this->assertTrue($profile->isComplete());
        $this->assertFalse($this->checker()->canGenerate($profile));
    }
}
