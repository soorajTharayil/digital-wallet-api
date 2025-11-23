<?php

namespace Database\Factories;

use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    public function definition(): array
    {
        return [
            'base_currency' => 'USD',
            'target_currency' => 'USD',
            'rate' => 1.0,
        ];
    }

    public function usdToInr(): static
    {
        return $this->state(fn (array $attributes) => [
            'base_currency' => 'USD',
            'target_currency' => 'INR',
            'rate' => 83.10,
        ]);
    }

    public function usdToEur(): static
    {
        return $this->state(fn (array $attributes) => [
            'base_currency' => 'USD',
            'target_currency' => 'EUR',
            'rate' => 0.92,
        ]);
    }

    public function inrToUsd(): static
    {
        return $this->state(fn (array $attributes) => [
            'base_currency' => 'INR',
            'target_currency' => 'USD',
            'rate' => 0.012,
        ]);
    }

    public function eurToUsd(): static
    {
        return $this->state(fn (array $attributes) => [
            'base_currency' => 'EUR',
            'target_currency' => 'USD',
            'rate' => 1.09,
        ]);
    }
}

