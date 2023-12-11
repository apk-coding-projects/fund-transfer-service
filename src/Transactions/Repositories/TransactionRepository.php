<?php

declare(strict_types=1);

namespace src\Transactions\Repositories;

use src\Accounts\Models\Account;
use src\Transactions\Models\Transaction;

class TransactionRepository
{
    public function save(Transaction $transaction): void
    {
        $transaction->save();
    }

    /** @return Account[] */
    public function getPaginatedByAccountId(int $accountId, int $limit, int $offset): array
    {
        $query = Transaction::
        where('receiver_account_id', $accountId)
            ->orWhere('sender_account_id', $accountId)
            ->orderByDesc('id');

        if ($limit) {
            $query->limit($limit);
        }

        if ($offset) {
            $query->offset($offset);
        }

        return $query->get()->all();
    }
}
