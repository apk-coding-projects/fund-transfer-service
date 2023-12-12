<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use src\Accounts\Models\Account;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\Structures\Currency;

class AccountHelper
{
    public static function build(
        float $amount = 10.50,
        bool $shouldSave = true,
        string $currency = Currency::CURRENCY_USD,
    ): Account {
        $account = new Account();
        $account->client_id = rand(1, 100);
        $account->currency = $currency;
        $account->amount = $amount;

        if ($shouldSave) {
            $account->save();
        }

        return $account;
    }
}
