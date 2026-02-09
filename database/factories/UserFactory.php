<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
        return [
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'bio' => fake()->optional()->sentence(),
            'image' => null,
            'points' => fake()->numberBetween(0, 100),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'mac_address' => null,
            'last_login_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'last_login_ip' => fake()->optional()->ipv4(),
            'last_login_user_agent' => fake()->optional()->userAgent(),
        ];
    }
}
