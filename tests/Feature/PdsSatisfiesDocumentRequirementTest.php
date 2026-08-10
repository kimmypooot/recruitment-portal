<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Models\PdsDeclaration;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Phase 6 of the PDS rollout: a PDS generated from the profile counts as the
 * required document, so applicants no longer have to hand-fill and upload one.
 * The uploaded route stays supported for anyone mid-flight.
 */
class PdsSatisfiesDocumentRequirementTest extends TestCase
{
    use RefreshDatabase;

    private function readyProfile(User $user): ApplicantProfile
    {
        $profile = ApplicantProfile::factory()->complete()->pdsPersonalInfo()->create(['user_id' => $user->id]);

        $profile->familyBackground()->create([
            'father_surname'           => 'Dela Cruz',
            'father_first_name'        => 'Pedro',
            'mother_maiden_surname'    => 'Reyes',
            'mother_maiden_first_name' => 'Maria',
        ]);
        $profile->educationalAttainments()->create(['level' => 'elementary', 'school_name' => 'Tacloban Elem.']);
        $profile->educationalAttainments()->create(['level' => 'secondary', 'school_name' => 'Tacloban High']);
        $profile->declaration()->create(array_fill_keys(PdsDeclaration::REQUIRED_ANSWERS, false));
        foreach (range(1, 3) as $i) {
            $profile->references()->create(['name' => "Reference {$i}", 'sort_order' => $i]);
        }

        // The other three required documents, uploaded as usual.
        $profile->forceFill([
            'app_letter_path' => 'profile-documents/x/app_letter.pdf',
            'coe_path'        => 'profile-documents/x/coe.pdf',
            'tor_path'        => 'profile-documents/x/tor.pdf',
        ])->save();

        return $profile->fresh();
    }

    public function test_an_uploaded_pds_still_satisfies_the_requirement(): void
    {
        $profile = ApplicantProfile::factory()->withDocuments()->create();

        $this->assertTrue($profile->hasPds());
        $this->assertTrue($profile->hasRequiredDocuments());
    }

    public function test_without_any_pds_the_requirement_is_unmet(): void
    {
        $profile = $this->readyProfile(User::factory()->applicant()->create());

        $this->assertFalse($profile->hasPds());
        $this->assertFalse($profile->hasRequiredDocuments());
    }

    public function test_generating_a_pds_stores_it_and_satisfies_the_requirement(): void
    {
        $user    = User::factory()->applicant()->create();
        $profile = $this->readyProfile($user);

        $this->actingAs($user, 'sanctum')
            ->get('/api/profile/pds/download?format=xlsx')
            ->assertOk();

        $fresh = $profile->fresh();

        $this->assertNotNull($fresh->pds_generated_path, 'the generated PDS was not retained');
        $this->assertTrue(Storage::disk('local')->exists($fresh->pds_generated_path));
        $this->assertTrue($fresh->hasPds());
        $this->assertTrue($fresh->hasRequiredDocuments());
    }

    public function test_regenerating_replaces_the_previous_copy_rather_than_accumulating(): void
    {
        $user    = User::factory()->applicant()->create();
        $profile = $this->readyProfile($user);

        $this->actingAs($user, 'sanctum')->get('/api/profile/pds/download?format=xlsx')->assertOk();
        $first = $profile->fresh()->pds_generated_path;

        // A later second within the same run, so the timestamped name differs.
        $this->travel(1)->second();

        $this->actingAs($user, 'sanctum')->get('/api/profile/pds/download?format=xlsx')->assertOk();
        $second = $profile->fresh()->pds_generated_path;

        $this->assertNotSame($first, $second);
        $this->assertFalse(Storage::disk('local')->exists($first), 'the superseded copy was left behind');
        $this->assertTrue(Storage::disk('local')->exists($second));
    }

    public function test_an_applicant_with_a_generated_pds_can_submit_an_application(): void
    {
        $user    = User::factory()->applicant()->create();
        $profile = $this->readyProfile($user);
        $vacancy = Vacancy::factory()->published()->create();

        $this->actingAs($user, 'sanctum')->get('/api/profile/pds/download?format=xlsx')->assertOk();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id])
            ->assertCreated();

        $this->assertDatabaseHas('applications', [
            'vacancy_id'   => $vacancy->id,
            'applicant_id' => $profile->id,
        ]);
    }

    public function test_a_board_download_does_not_overwrite_the_applicants_pds_of_record(): void
    {
        $user    = User::factory()->applicant()->create();
        $profile = $this->readyProfile($user);

        $this->actingAs($user, 'sanctum')->get('/api/profile/pds/download?format=xlsx')->assertOk();
        $applicantCopy = $profile->fresh()->pds_generated_path;

        $application = Application::create([
            'vacancy_id'   => Vacancy::factory()->published()->create()->id,
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
        ]);

        $this->travel(1)->second();

        $this->actingAs(User::factory()->admin()->create(), 'sanctum')
            ->get("/api/hrmpsb/applications/{$application->id}/pds")
            ->assertOk();

        $this->assertSame($applicantCopy, $profile->fresh()->pds_generated_path);
        $this->assertTrue(Storage::disk('local')->exists($applicantCopy));
    }
}
