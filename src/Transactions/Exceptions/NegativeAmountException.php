<?php

declare(strict_types=1);

namespace src\Transactions\Exceptions;

class NegativeAmountException extends TransactionException
{
    protected $message = 'Transaction amount cannot be negative!';
}
