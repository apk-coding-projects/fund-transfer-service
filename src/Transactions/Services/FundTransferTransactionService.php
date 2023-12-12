<?php

declare(strict_types=1);

namespace src\Transactions\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use src\Accounts\Services\AccountService;
use src\Common\Exceptions\LockAlreadyAcquiredException;
use src\Common\Helpers\CacheLockHelper;
use src\CurrencyRates\Exceptions\RateNotFoundException;
use src\CurrencyRates\Services\CurrencyConversionService;
use src\Transactions\Exceptions\NegativeAmountException;
use src\Transactions\Exceptions\NotEnoughBalanceException;
use src\Transactions\Exceptions\TransactionException;
use src\Transactions\Factories\TransactionFactory;
use src\Transactions\Interfaces\TransactionInterface;
use src\Transactions\Models\Transaction;
use src\Transactions\Repositories\TransactionRepository;
use src\Transactions\Structures\FundTransferRequest;
use src\Transactions\Structures\TransactionRequest;
use Throwable;

class FundTransferTransactionService implements TransactionInterface
{
    private const LOCK_KEY_PREFIX = 'FUND_TRANSFER';
    private const SECONDS_ONE_MINUTE = 60;

    public function __construct(
        private readonly TransactionValidationService $validationService,
        private readonly AccountService $accountService,
        private readonly CurrencyConversionService $conversionService,
        private readonly TransactionRepository $transactionRepository,
        private readonly TransactionFactory $transactionFactory,
    ) {
    }

    /**
     * @param  FundTransferRequest  $request
     * @throws RateNotFoundException
     * @throws TransactionException
     * @throws Throwable
     */
    public function transfer(TransactionRequest $request): void
    {
        $transaction = $this->transactionFactory->preparePending($request);
        $this->transactionRepository->save($transaction);

        $lockKey = $this->getLockKey($request);

        try {
            $this->startProcessing($lockKey);

            $this->performTransfer($request);

            $this->finalize($transaction, Transaction::STATUS_SUCCESS);

            $this->endProcessing($lockKey, false);
        } catch (Throwable $throwable) {
            $this->endProcessing($lockKey, true);

            Log::error($throwable->getTraceAsString());

            $this->finalize($transaction, Transaction::STATUS_FAILURE);

            throw $throwable;
        }
    }

    /**
     * @param  FundTransferRequest  $request
     * @throws RateNotFoundException
     */
    public function getSenderTransferAmount(TransactionRequest $request): float
    {
        $amount = $request->amount;
        $senderAccount = $request->senderAccount;
        $receiverAccount = $request->receiverAccount;

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

    private function finalize(Transaction $transaction, string $status): void
    {
        $transaction->status = $status;
        $this->transactionRepository->save($transaction);
    }

    /**
     * @throws RateNotFoundException
     * @throws NegativeAmountException
     * @throws NotEnoughBalanceException
     */
    private function performTransfer(FundTransferRequest $request): void
    {
        $amountToAdd = $request->amount;
        $amountToSubtract = $this->getSenderTransferAmount($request);

        $this->accountService->subtractFromAccount($request->senderAccount, $amountToSubtract);
        $this->accountService->addToAccount($request->receiverAccount, $amountToAdd);
    }

    private function getLockKey(FundTransferRequest $request): string
    {
        return join(
            '_',
            [
                self::LOCK_KEY_PREFIX,
                $request->senderAccount->id,
                $request->receiverAccount->id,
                $request->amount,
                $request->currency,
            ]
        );
    }

    /**  @throws LockAlreadyAcquiredException */
    private function startProcessing(string $lockKey): void
    {
        DB::beginTransaction();
        CacheLockHelper::acquire($lockKey, self::SECONDS_ONE_MINUTE);
    }

    private function endProcessing(string $lockKey, bool $shouldRollback): void
    {
        if ($shouldRollback) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        CacheLockHelper::release($lockKey);
    }
}
