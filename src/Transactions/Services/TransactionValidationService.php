<?php

declare(strict_types=1);

namespace src\Transactions\Services;

use src\Accounts\Models\Account;
use src\CurrencyRates\Exceptions\RateNotFoundException;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\Services\CurrencyConversionService;
use src\Transactions\Exceptions\CurrencyNotSupportedException;
use src\Transactions\Exceptions\NegativeAmountException;
use src\Transactions\Exceptions\NotEnoughBalanceException;
use src\Transactions\Exceptions\ReceiverCurrencyException;
use src\Transactions\Exceptions\TransactionException;
use src\Transactions\Structures\FundTransferRequest;
use src\Transactions\Structures\TransactionRequest;

class TransactionValidationService
{
    public function __construct(
        private readonly CurrencyConversionService $conversionService,
    ) {
    }

    /**
     * @param  FundTransferRequest  $request
     * @throws TransactionException|RateNotFoundException
     */
    public function validate(TransactionRequest $request): void
    {
        $this->validateCurrency($request, $request->receiverAccount);

        $this->validateAmount($request, $request->senderAccount, $request->receiverAccount);
    }

    /**
     * @throws CurrencyNotSupportedException
     * @throws ReceiverCurrencyException
     */
    private function validateCurrency(TransactionRequest $request, ?Account $receiverAccount): void
    {
        if (!in_array($request->currency, CurrencyRate::SUPPORTED_CURRENCIES)) {
            throw new CurrencyNotSupportedException();
        }

        if ($receiverAccount->currency !== $request->currency) {
            throw new ReceiverCurrencyException();
        }
    }

    /**
     * @throws NegativeAmountException
     * @throws NotEnoughBalanceException
     * @throws RateNotFoundException
     */
    private function validateAmount(
        TransactionRequest $request,
        ?Account $senderAccount,
        ?Account $receiverAccount,
    ): void {
        $amount = $request->amount;
        if ($amount <= 0) {
            throw new NegativeAmountException();
        }

        if ($senderAccount->currency !== $receiverAccount->currency) {
            $amount = $this->conversionService->convert(
                $receiverAccount->currency,
                $senderAccount->currency,
                date('Y-m-d'),
                $request->amount
            );
        }

        if ($senderAccount->amount < $amount) {
            throw new NotEnoughBalanceException();
        }
    }
}
