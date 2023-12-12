<?php

declare(strict_types=1);

namespace src\Transactions\Repositories;

use src\Transactions\Models\Transaction;

class TransactionRepository
{
    public function save(Transaction $transaction): void
    {
        $transaction->save();
    }

    public function getPaginatedByAccountId(int $accountId, int $limit = 0, int $offset = 0): array
    {
        $query = Transaction::where('receiver_account_id', $accountId)
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
