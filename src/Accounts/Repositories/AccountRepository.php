<?php

declare(strict_types=1);

namespace src\Accounts\Repositories;

use src\Accounts\Models\Account;

class AccountRepository
{
    public function getById(int $accountId): ?Account
    {
        return Account::where('id', $accountId)->first();
    }

    /** @return Account[] */
    public function getByClientId(int $clientId): array
    {
        return Account::where('client_id', $clientId)->get()->all();
    }
}
