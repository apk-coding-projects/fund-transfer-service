<?php

declare(strict_types=1);

namespace src\Transactions\Exceptions;

class TransactionResolverMissingException extends TransactionException
{
    protected $message = 'Resolver for the provided Request params is missing!';
}
