<?php

declare(strict_types=1);

namespace Tests\Feature\Transactions\Services;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Redis;
use src\Common\Exceptions\LockAlreadyAcquiredException;
use src\Transactions\Exceptions\CurrencyNotSupportedException;
use src\Transactions\Models\Transaction;
use src\Transactions\Services\FundTransferTransactionService;
use src\Transactions\Structures\FundTransferRequest;
use Tests\Feature\Helpers\AccountHelper;
use Tests\Feature\RateImport\Helpers\CurrencyRateHelper;
use Tests\TestCase;

class FundTransferTransactionServiceTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    private const LOCK_KEY_PREFIX = 'FUND_TRANSFER';

    private FundTransferTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FundTransferTransactionService::class);
    }

    public function testValidate_withValidData_processes(): void
    {
        $request = $this->getRequest(1, 'USD');

        $this->service->validate($request);

        self::expectNotToPerformAssertions();
    }

    public function testValidate_withNotSupportedCurrency_throwsException(): void
    {
        $request = $this->getRequest(100, 'XYZ?');

        self::expectException(CurrencyNotSupportedException::class);
        self::expectExceptionMessage('Currency is not supported in the system');

        $this->service->validate($request);
    }

    public function testGetSenderTransferAmount_withSameCurrency_returnsSameAmount(): void
    {
        $request = $this->getRequest(100, 'USD');

        $amount = $this->service->getSenderTransferAmount($request);

        self::assertEquals(100, $amount);
    }

    public function testGetSenderTransferAmount_withDiffCurrency_returnsCorrectAmount(): void
    {
        $rate = 0.5;
        $sender = AccountHelper::build(1000);
        $receiver = AccountHelper::build(1400, currency: 'AUD');

        $request = new FundTransferRequest(100, 'AUD', $sender, $receiver);

        $currencyRate = CurrencyRateHelper::build('USD', 'AUD', date('Y-m-d'), $rate);
        Redis::shouldReceive('get')
            ->once()
            ->andReturn(json_encode($currencyRate->toArray()));

        $amount = $this->service->getSenderTransferAmount($request);

        self::assertEquals(round(100 * $rate, 2), $amount);
    }

    public function testTransfer_withLockAlreadyAcquired_createsFailedTransaction(): void
    {
        $request = $this->getRequest(100, 'USD');

        $key = $this->getLockKey($request);
        Redis::shouldReceive('get')
            ->with($key)
            ->once()
            ->andReturn(true);
        Redis::shouldReceive('del')
            ->with($key)
            ->once();

        self::expectException(LockAlreadyAcquiredException::class);

        $txBefore = Transaction::count();
        $senderBalanceBefore = $request->senderAccount->amount;
        $receiverBalanceBefore = $request->receiverAccount->amount;

        $this->service->transfer($request);
        $txAfter = Transaction::count();

        /** @var Transaction $createdTransaction */
        $createdTransaction = Transaction::first();

        self::assertNotEquals($txBefore + 1, $txAfter);
        self::assertEquals(Transaction::STATUS_FAILURE, $createdTransaction->status);
        self::assertEquals(100, $createdTransaction->amount);
        self::assertEquals('USD', $createdTransaction->currency);
        self::assertEquals($request->senderAccount->id, $createdTransaction->sender_account_id);
        self::assertEquals($request->receiverAccount->id, $createdTransaction->receiver_account_id);
        self::assertEquals($senderBalanceBefore, $createdTransaction->senderAccount->amount);
        self::assertEquals($receiverBalanceBefore, $createdTransaction->receiverAccount->amount);
    }

    public function testTransfer_withSameCurrencyAccounts_transfersFunds(): void
    {
        $request = $this->getRequest(100, 'USD');

        $key = $this->getLockKey($request);
        Redis::shouldReceive('get')
            ->with($key)
            ->once()
            ->andReturn(null);
        Redis::shouldReceive('setex')
            ->once();
        Redis::shouldReceive('del')
            ->with($key)
            ->once();

        $txBefore = Transaction::count();
        $senderBalanceBefore = $request->senderAccount->amount;
        $receiverBalanceBefore = $request->receiverAccount->amount;

        $this->service->transfer($request);
        $txAfter = Transaction::count();

        /** @var Transaction $createdTransaction */
        $createdTransaction = Transaction::first();

        self::assertEquals($txBefore + 1, $txAfter);
        self::assertEquals(Transaction::STATUS_SUCCESS, $createdTransaction->status);
        self::assertEquals(100, $createdTransaction->amount);
        self::assertEquals('USD', $createdTransaction->currency);
        self::assertEquals($request->senderAccount->id, $createdTransaction->sender_account_id);
        self::assertEquals($request->receiverAccount->id, $createdTransaction->receiver_account_id);
        self::assertEquals($senderBalanceBefore - 100, $createdTransaction->senderAccount->amount);
        self::assertEquals($receiverBalanceBefore + 100, $createdTransaction->receiverAccount->amount);
    }

    public function testTransfer_withDifferentCurrencyAccounts_transfersConvertedFunds(): void
    {
        $rate = 0.5;
        $from = 'USD';
        $to = 'AUD';
        $date = date('Y-m-d');
        $amountToTransfer = 100;

        $sender = AccountHelper::build(1000);
        $receiver = AccountHelper::build(1400, currency: $to);

        $request = new FundTransferRequest($amountToTransfer, $to, $sender, $receiver);

        $currencyRate = CurrencyRateHelper::build($from, $to, $date, $rate);
        $currencyCacheKey = join('_', ['CACHED_CURRENCY_RATES', $to, $from, $date]);
        Redis::shouldReceive('get')
            ->with($currencyCacheKey)
            ->once()
            ->andReturn(json_encode($currencyRate->toArray()));

        $key = $this->getLockKey($request);
        Redis::shouldReceive('get')
            ->with($key)
            ->once()
            ->andReturn(null);
        Redis::shouldReceive('setex')
            ->once();
        Redis::shouldReceive('del')
            ->with($key)
            ->once();

        $txBefore = Transaction::count();
        $senderBalanceBefore = $request->senderAccount->amount;
        $receiverBalanceBefore = $request->receiverAccount->amount;

        $this->service->transfer($request);
        $txAfter = Transaction::count();

        /** @var Transaction $createdTransaction */
        $createdTransaction = Transaction::first();
        $convertedAmount = round($amountToTransfer * $rate, 2);

        self::assertEquals($txBefore + 1, $txAfter);
        self::assertEquals(Transaction::STATUS_SUCCESS, $createdTransaction->status);
        self::assertEquals($amountToTransfer, $createdTransaction->amount);
        self::assertEquals('AUD', $createdTransaction->currency);
        self::assertEquals($request->senderAccount->id, $createdTransaction->sender_account_id);
        self::assertEquals($request->receiverAccount->id, $createdTransaction->receiver_account_id);
        self::assertEquals($senderBalanceBefore - $convertedAmount, $createdTransaction->senderAccount->amount);
        self::assertEquals($receiverBalanceBefore + $amountToTransfer, $createdTransaction->receiverAccount->amount);
    }

    private function getRequest(float $amount, string $currency): FundTransferRequest
    {
        $sender = AccountHelper::build(1000);
        $receiver = AccountHelper::build(1400);

        return new FundTransferRequest($amount, $currency, $sender, $receiver);
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
}
