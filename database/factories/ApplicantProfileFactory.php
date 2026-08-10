<?php

namespace Database\Factories;

use App\Models\ApplicantProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicantProfile>
 */
class ApplicantProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->applicant(),
        ];
    }

    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender'         => fake()->randomElement(['Male', 'Female']),
            'civil_status'   => fake()->randomElement(['Single', 'Married', 'Widowed']),
            'birthday'       => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'mobile_number'  => '09' . fake()->numerify('#########'),
            'region'         => 'Region VIII',
            'eligibility'    => 'Career Service Professional',
        ]);
    }

    /**
     * Section I of CS Form No. 212 (Revised 2026), filled to the point where
     * PdsCompletenessChecker considers personal information complete.
     */
    public function pdsPersonalInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'place_of_birth'        => 'Tacloban City',
            'citizenship'           => 'filipino',
            'citizenship_basis'     => 'by_birth',
            'res_house_block_lot'   => '12',
            'res_street'            => 'Real Street',
            'res_barangay'          => 'Barangay 20',
            'res_city_municipality' => 'Tacloban City',
            'res_province'          => 'Leyte',
            'res_zip_code'          => '6500',
            'perm_barangay'         => 'Barangay 20',
            'perm_city_municipality' => 'Tacloban City',
            'perm_province'         => 'Leyte',
            'perm_zip_code'         => '6500',
        ]);
    }

    /**
     * Satisfies ApplicantProfile::hasRequiredDocuments() (PDS, application
     * letter, COE, TOR) so the profile can pass application submission.
     */
    public function withDocuments(): static
    {
        return $this->state(fn (array $attributes) => [
            'pds_path'        => 'profile-documents/test/pds.pdf',
            'app_letter_path' => 'profile-documents/test/app_letter.pdf',
            'coe_path'        => 'profile-documents/test/coe.pdf',
            'tor_path'        => 'profile-documents/test/tor.pdf',
        ]);
    }
}
