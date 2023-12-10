<?php

declare(strict_types=1);

namespace src\Transactions\Exceptions;

class NotEnoughBalanceException extends TransactionException
{
    protected $message = 'Transaction amount is larger than the available balance in the account!';
}
