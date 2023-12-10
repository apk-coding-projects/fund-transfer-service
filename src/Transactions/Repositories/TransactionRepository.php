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
}
