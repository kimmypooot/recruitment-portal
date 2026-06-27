<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();
        $parts = explode(' ', $name);

        return [
            'first_name'        => $parts[0] ?? '',
            'last_name'         => count($parts) > 1 ? $parts[count($parts) - 1] : '',
            'middle_name'       => count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : null,
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function applicant(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'applicant']);
    }

    public function hrOfficer(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }

    public function hrManager(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }

    public function hrmpsb(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'hrmpsb']);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }
}
