<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use src\Accounts\Models\Account;
use src\CurrencyRates\Models\CurrencyRate;

/**
 * @extends Factory<\App\Models\Model>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'currency' => Arr::random(CurrencyRate::SUPPORTED_CURRENCIES),
            'amount' => round(rand(100, 500000) / 10, 2),
        ];
    }
}
