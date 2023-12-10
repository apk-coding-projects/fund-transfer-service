<?php

declare(strict_types=1);

namespace src\Transactions\Exceptions;

class CurrencyNotSupportedException extends TransactionException
{
    protected $message = 'Currency is not supported in the system';
}
