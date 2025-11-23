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
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'), // Default password for testing
            'default_currency' => $this->faker->randomElement(['USD', 'INR', 'EUR']),
            'daily_debit_limit' => 10000.00,
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function withCurrency(string $currency): static
    {
        return $this->state(fn (array $attributes) => [
            'default_currency' => $currency,
        ]);
    }

    public function withDailyLimit(float $limit): static
    {
        return $this->state(fn (array $attributes) => [
            'daily_debit_limit' => $limit,
        ]);
    }
}

