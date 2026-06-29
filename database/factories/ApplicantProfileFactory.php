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
}
