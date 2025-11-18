<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['base_currency' => 'USD', 'target_currency' => 'USD', 'rate' => 1],
            ['base_currency' => 'USD', 'target_currency' => 'INR', 'rate' => 83.10],
            ['base_currency' => 'USD', 'target_currency' => 'EUR', 'rate' => 0.92],
            ['base_currency' => 'INR', 'target_currency' => 'USD', 'rate' => 0.012],
            ['base_currency' => 'INR', 'target_currency' => 'INR', 'rate' => 1],
            ['base_currency' => 'INR', 'target_currency' => 'EUR', 'rate' => 0.011],
            ['base_currency' => 'EUR', 'target_currency' => 'USD', 'rate' => 1.09],
            ['base_currency' => 'EUR', 'target_currency' => 'INR', 'rate' => 89.50],
            ['base_currency' => 'EUR', 'target_currency' => 'EUR', 'rate' => 1],
        ];

        foreach ($rates as $rate) {
            ExchangeRate::updateOrCreate(
                [
                    'base_currency' => $rate['base_currency'],
                    'target_currency' => $rate['target_currency'],
                ],
                ['rate' => $rate['rate']]
            );
        }
    }
}
