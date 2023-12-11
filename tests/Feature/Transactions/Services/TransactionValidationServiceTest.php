<?php

declare(strict_types=1);

namespace Tests\Feature\Transactions\Services;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Redis;
use src\Transactions\Exceptions\CurrencyNotSupportedException;
use src\Transactions\Exceptions\NegativeAmountException;
use src\Transactions\Exceptions\NotEnoughBalanceException;
use src\Transactions\Exceptions\ReceiverCurrencyException;
use src\Transactions\Services\TransactionValidationService;
use src\Transactions\Structures\FundTransferRequest;
use Tests\Feature\Helpers\AccountHelper;
use Tests\Feature\RateImport\Helpers\CurrencyRateHelper;
use Tests\TestCase;

class TransactionValidationServiceTest extends TestCase
{
    use DatabaseMigrations;

    private TransactionValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TransactionValidationService::class);
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

    public function testValidate_withNotReceiversCurrency_throwsException(): void
    {
        $request = $this->getRequest(100, 'AUD');

        self::expectException(ReceiverCurrencyException::class);
        self::expectExceptionMessage('Currency is not supported by transaction receiver');

        $this->service->validate($request);
    }

    public function testValidate_withNegativeAmount_throwsException(): void
    {
        $request = $this->getRequest(-100, 'USD');

        self::expectException(NegativeAmountException::class);
        self::expectExceptionMessage('Transaction amount cannot be negative!');

        $this->service->validate($request);
    }

    public function testValidate_withSenderNotEnoughBalance_throwsException(): void
    {
        $request = $this->getRequest(1000000, 'USD');

        self::expectException(NotEnoughBalanceException::class);
        self::expectExceptionMessage('Transaction amount is larger than the available balance in the account!');

        $this->service->validate($request);
    }

    public function testValidate_withSenderNotEnoughBalanceConvertedAmount_throwsException(): void
    {
        $sender = AccountHelper::build(1000);
        $receiver = AccountHelper::build(1400, currency: 'AUD');

        $request = new FundTransferRequest(10000000, 'AUD', $sender, $receiver);

        $currencyRate = CurrencyRateHelper::build('USD', 'AUD', date('Y-m-d'), 0.89);
        Redis::shouldReceive('get')
            ->once()
            ->andReturn(json_encode($currencyRate->toArray()));

        self::expectException(NotEnoughBalanceException::class);
        self::expectExceptionMessage('Transaction amount is larger than the available balance in the account!');

        $this->service->validate($request);
    }

    private function getRequest(float $amount, string $currency): FundTransferRequest
    {
        $sender = AccountHelper::build(1000);
        $receiver = AccountHelper::build(1400);

        return new FundTransferRequest($amount, $currency, $sender, $receiver);
    }
}
