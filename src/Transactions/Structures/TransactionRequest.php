<?php

declare(strict_types=1);

namespace src\Transactions\Structures;

class TransactionRequest
{
    public function __construct(
        public float $amount,
        public string $currency,
    )
    {
    }
}
