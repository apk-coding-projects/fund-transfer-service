<?php

declare(strict_types=1);

namespace src\Transactions\Factories;

use src\Transactions\Models\Transaction;
use src\Transactions\Structures\FundTransferRequest;

class TransactionFactory
{
    public function preparePending(FundTransferRequest $request): Transaction
    {
        $transaction = new Transaction();
        $transaction->amount = $request->amount;
        $transaction->currency = $request->currency;
        $transaction->sender_account_id = $request->senderAccount->id;
        $transaction->receiver_account_id = $request->receiverAccount->id;
        $transaction->status = Transaction::STATUS_PROCESSING;

        return $transaction;
    }
}
