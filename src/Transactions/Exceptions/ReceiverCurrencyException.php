<?php

declare(strict_types=1);

namespace src\Transactions\Exceptions;

class ReceiverCurrencyException extends TransactionException
{
    protected $message = 'Currency is not supported by transaction receiver';
}
