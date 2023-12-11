<?php

declare(strict_types=1);

namespace Tests\Feature\Transactions\Helpers;

use src\Transactions\Models\Transaction;

class TransactionHelper
{
    public static function build(int $accountFrom = 999, int $accountTo = 111): Transaction
    {
        $transaction = new Transaction();
        $transaction->amount = 1234;
        $transaction->currency = 'USD';
        $transaction->sender_account_id = $accountFrom;
        $transaction->receiver_account_id = $accountTo;
        $transaction->status = Transaction::STATUS_PROCESSING;

        return $transaction;
    }
}
