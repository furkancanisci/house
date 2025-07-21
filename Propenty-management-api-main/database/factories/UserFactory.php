<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'bio' => fake()->paragraph(),
            'user_type' => fake()->randomElement(['property_owner', 'general_user']),
            'is_verified' => true,
            'is_active' => true,
            'last_login_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'is_verified' => false,
        ]);
    }

    /**
     * Indicate that the user is a property owner.
     */
    public function propertyOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'property_owner',
        ]);
    }

    /**
     * Indicate that the user is a general user.
     */
    public function generalUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'general_user',
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}