<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use src\Accounts\Models\Account;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\Structures\Currency;
use src\Transactions\Models\Transaction;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $currency = Arr::random([Currency::CURRENCY_USD, Currency::CURRENCY_EUR, Currency::CURRENCY_NZD]);

        $sender = $this->faker->randomElement(Account::where('currency', $currency)->pluck('id'));

        $receiver = $this->faker->randomElement(Account::where('currency', $currency)->pluck('id'));

        // ids cannot be the same
        while ($sender === $receiver) {
            $receiver = $this->faker->randomElement(Account::where('currency', $currency)->pluck('id'));
        }

        return [
            'sender_account_id' => $sender,
            'receiver_account_id' => $receiver,
            'currency' => $currency,
            'amount' => rand(10,150),
            'status' => Arr::random([Transaction::STATUS_SUCCESS, Transaction::STATUS_FAILURE]),
        ];
    }
}
