<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApplicantProfile;
use App\Models\User;
use App\Services\Pds\PdsCompletenessChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The PDS Details tab writes Section I through the existing profile endpoint,
 * so UpdateProfileRequest has to accept the new fields — otherwise they are
 * silently dropped by validated() and the form generates with N/A everywhere.
 */
class PdsSectionOneProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): array
    {
        $user    = User::factory()->applicant()->create();
        $profile = ApplicantProfile::factory()->complete()->create(['user_id' => $user->id]);

        return [$user, $profile];
    }

    public function test_section_one_fields_are_persisted(): void
    {
        [$user, $profile] = $this->actor();

        $payload = [
            'place_of_birth'           => 'Tacloban City',
            'height_m'                 => 1.65,
            'weight_kg'                => 60.5,
            'blood_type'               => 'O+',
            'citizenship'              => 'filipino',
            'philsys_number'           => '2059-8569-1428-1039',
            'umid_id_no'               => '0123-4567890-1',
            'telephone_no'             => '(053) 000-0000',
            'res_house_block_lot'      => '12',
            'res_street'               => 'Real Street',
            'res_barangay'             => 'Barangay 20',
            'res_city_municipality'    => 'Tacloban City',
            'res_province'             => 'Leyte',
            'res_zip_code'             => '6500',
            'perm_barangay'            => 'Barangay 20',
            'perm_city_municipality'   => 'Tacloban City',
            'perm_province'            => 'Leyte',
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', $payload)
            ->assertOk();

        $fresh = $profile->fresh();

        foreach (['place_of_birth', 'blood_type', 'citizenship', 'philsys_number', 'res_zip_code'] as $field) {
            $this->assertSame($payload[$field], $fresh->{$field}, "{$field} was not saved");
        }

        $this->assertSame('1.65', (string) $fresh->height_m);
        $this->assertSame('Tacloban City', $fresh->perm_city_municipality);
    }

    public function test_citizenship_only_accepts_the_two_values_the_form_prints(): void
    {
        [$user] = $this->actor();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', ['citizenship' => 'martian'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('citizenship');
    }

    public function test_filling_section_one_clears_it_from_the_completeness_blockers(): void
    {
        [$user, $profile] = $this->actor();

        $checker = app(PdsCompletenessChecker::class);
        $this->assertContains('personal_information', $checker->check($profile)['blocking']);

        $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
            'place_of_birth'         => 'Tacloban City',
            'citizenship'            => 'filipino',
            'res_city_municipality'  => 'Tacloban City',
            'perm_city_municipality' => 'Tacloban City',
        ])->assertOk();

        $result = $checker->check($profile->fresh());

        $this->assertNotContains('personal_information', $result['blocking']);
    }
}
