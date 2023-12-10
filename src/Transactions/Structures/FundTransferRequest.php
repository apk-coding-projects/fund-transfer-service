<?php

declare(strict_types=1);

namespace src\Transactions\Structures;

use src\Accounts\Models\Account;

class FundTransferRequest extends TransactionRequest
{
    public function __construct(
        public float $amount,
        public string $currency,
        public Account $senderAccount,
        public Account $receiverAccount,
    ) {
        parent::__construct($this->amount, $this->currency);
    }
}
