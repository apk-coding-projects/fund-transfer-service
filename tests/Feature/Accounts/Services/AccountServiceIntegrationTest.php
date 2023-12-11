<?php

declare(strict_types=1);

namespace Tests\Feature\Accounts\Services;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use src\Accounts\Services\AccountService;
use src\Transactions\Exceptions\NegativeAmountException;
use src\Transactions\Exceptions\NotEnoughBalanceException;
use Tests\Feature\Helpers\AccountHelper;
use Tests\TestCase;

class AccountServiceIntegrationTest extends TestCase
{
    use DatabaseMigrations;

    private AccountService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AccountService::class);
    }

    public function testAddToAccount_withNegativeAmount_throwsException(): void
    {
        $account = AccountHelper::build();

        self::expectException(NegativeAmountException::class);
        self::expectExceptionMessage('Cannot add negative amount to account: -14');

        $this->service->addToAccount($account, -14);
    }

    public function testAddToAccount_withPositiveAmount_updatesModel(): void
    {
        $account = AccountHelper::build(100);

        $this->service->addToAccount($account, 10);
        $account->refresh();

        self::assertEquals(110, $account->amount);
    }

    public function testSubtractFromAccount_withNegativeAmount_throwsException(): void
    {
        $account = AccountHelper::build();

        self::expectException(NegativeAmountException::class);
        self::expectExceptionMessage('Cannot add negative amount to account: -14');

        $this->service->subtractFromAccount($account, -14);
    }

    public function testSubtractFromAccount_withNotEnoughBalanceToSubtract_throwsException(): void
    {
        $account = AccountHelper::build(10);

        self::expectException(NotEnoughBalanceException::class);
        self::expectExceptionMessage('Not enough balance in the account!');

        $this->service->subtractFromAccount($account, 100);
    }

    public function testSubtractFromAccount_withPositiveAmount_updatesModel(): void
    {
        $account = AccountHelper::build(100);

        $this->service->subtractFromAccount($account, 10);
        $account->refresh();

        self::assertEquals(90, $account->amount);
    }
}
