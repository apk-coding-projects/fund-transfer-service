<?php

namespace Tests\Feature\Transactions\Services;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Redis;
use src\Transactions\Exceptions\TransactionResolverMissingException;
use src\Transactions\Models\Transaction;
use src\Transactions\Services\TransactionService;
use src\Transactions\Structures\FundTransferRequest;
use src\Transactions\Structures\TransactionRequest;
use Tests\Feature\Helpers\AccountHelper;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;
    
    private TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TransactionService::class);
    }

    public function testMakeTransaction_withBaseParametersWithoutMapping_throwsException()
    {
        $params = new TransactionRequest(12, 'xyz');
        $this->expectException(TransactionResolverMissingException::class);

        $this->service->makeTransaction($params);
    }

    public function testMakeTransaction_withSameCurrencyAccounts_transfersFunds(): void
    {
        $sender = AccountHelper::build(1000);
        $receiver = AccountHelper::build(1400);

        $request = new FundTransferRequest(100, 'USD', $sender, $receiver);

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

        $this->service->makeTransaction($request);
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

    private function getLockKey(FundTransferRequest $request): string
    {
        return join(
            '_',
            [
                'FUND_TRANSFER',
                $request->senderAccount->id,
                $request->receiverAccount->id,
                $request->amount,
                $request->currency,
            ]
        );
    }
}
