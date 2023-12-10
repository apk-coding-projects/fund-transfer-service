<?php

declare(strict_types=1);

namespace src\Transactions\Services;

use src\Accounts\Repositories\AccountRepository;
use src\CurrencyRates\Exceptions\RateNotFoundException;
use src\CurrencyRates\Services\CurrencyConversionService;
use src\Transactions\Exceptions\NegativeAmountException;
use src\Transactions\Exceptions\TransactionException;
use src\Transactions\Interfaces\TransactionInterface;
use src\Transactions\Structures\FundTransferRequest;
use src\Transactions\Structures\TransactionRequest;

class FundTransferTransactionService implements TransactionInterface
{
    public function __construct(
        private readonly TransactionValidationService $validationService,
        private readonly AccountRepository $accountRepository,
        private readonly CurrencyConversionService $conversionService,
    ) {
    }

    /**
     * @param  FundTransferRequest  $request
     * @throws TransactionException
     * @throws RateNotFoundException
     */

    public function transfer(TransactionRequest $request): void
    {
        $this->validate($request);

        $amount = $this->getAmount($request);
    }

    /**
     * @param  FundTransferRequest  $request
     * @throws RateNotFoundException
     */
    public function getAmount(TransactionRequest $request): float
    {
        $senderAccount = $this->accountRepository->getById($request->senderAccountId);
        $receiverAccount = $this->accountRepository->getById($request->receiverAccountId);

        $amount = $request->amount;

        if ($senderAccount->currency !== $receiverAccount->currency) {
            $amount = $this->conversionService->convert(
                $receiverAccount->currency,
                $senderAccount->currency,
                date('Y-m-d'),
                $request->amount
            );
        }

        return $amount;
    }

    /**
     * @param  FundTransferRequest  $request
     *
     * @throws TransactionException
     * @throws RateNotFoundException
     */
    public function validate(TransactionRequest $request): void
    {
        $this->validationService->validate($request);
    }
}
