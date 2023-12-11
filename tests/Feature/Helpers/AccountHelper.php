<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use src\Accounts\Models\Account;
use src\CurrencyRates\Models\CurrencyRate;

class AccountHelper
{
    public static function build(float $amount = 10.50, bool $shouldSave = true): Account
    {
        $account = new Account();
        $account->client_id = rand(1,100);
        $account->currency = CurrencyRate::CURRENCY_USD;
        $account->amount = $amount;

        if ($shouldSave) {
            $account->save();
        }

        return $account;
    }
}