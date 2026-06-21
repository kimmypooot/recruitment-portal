<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacancy>
 */
class VacancyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'position_title'      => fake()->jobTitle(),
            'item_number'         => strtoupper(fake()->unique()->bothify('ITEM-####')),
            'salary_grade'        => fake()->numberBetween(1, 33),
            'plantilla_number'    => fake()->optional()->numerify('PLANTILLA-###'),
            'place_of_assignment' => fake()->city(),
            'education_req'       => 'Bachelor\'s Degree relevant to the job',
            'experience_req'      => '1 year of relevant experience',
            'training_req'        => '4 hours of relevant training',
            'eligibility_req'     => 'Career Service (Professional) / Second Level Eligibility',
            'status'              => 'draft',
            'posted_by'           => User::factory()->hrOfficer(),
            'published_at'        => null,
            'deadline_at'         => now()->addDays(10),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'published',
            'published_at' => now(),
            'deadline_at'  => now()->addDays(10),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'closed',
            'published_at' => now()->subDays(20),
            'deadline_at'  => now()->subDays(10),
        ]);
    }
}
