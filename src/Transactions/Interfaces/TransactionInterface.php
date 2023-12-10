<?php

namespace src\Transactions\Interfaces;

use src\Transactions\Structures\TransactionRequest;

interface TransactionInterface
{
    public function transfer(TransactionRequest $request): void;

    public function getAmount(TransactionRequest $request): float;

    public function validate(TransactionRequest $request): void;
}
