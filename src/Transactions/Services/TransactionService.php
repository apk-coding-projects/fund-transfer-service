<?php

declare(strict_types=1);

namespace src\Transactions\Services;

use Illuminate\Support\Facades\App;
use src\Transactions\Exceptions\TransactionResolverMissingException;
use src\Transactions\Interfaces\TransactionInterface;
use src\Transactions\Structures\FundTransferRequest;
use src\Transactions\Structures\TransactionRequest;

class TransactionService
{
    /** @var string[] Maps Tx requests with resolver service that will be used to make transaction */
    public const TRANSACTION_PARAMETER_RESOLVER_MAPPING = [
        FundTransferRequest::class => FundTransferTransactionService::class,
        // ...
    ];

    /**  @throws TransactionResolverMissingException */
    public function makeTransaction(TransactionRequest $request): void
    {
        $resolverClass = $this->getResolverService(get_class($request));

        /** @var TransactionInterface $resolver */
        $resolver = App::make($resolverClass);

        $resolver->validate($request);
        $resolver->transfer($request);
    }

    /** @throws TransactionResolverMissingException */
    private function getResolverService(string $requestClass): string
    {
        $resolver = self::TRANSACTION_PARAMETER_RESOLVER_MAPPING[$requestClass] ?? null;
        if ($resolver) {
            return $resolver;
        }

        throw new TransactionResolverMissingException();
    }
}
