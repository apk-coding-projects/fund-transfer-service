<?php

declare(strict_types=1);

namespace Tests\Feature\Accounts\Repositories;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use src\Accounts\Models\Account;
use src\Accounts\Repositories\AccountRepository;
use Tests\TestCase;

class AccountRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    private AccountRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(AccountRepository::class);
    }

    public function testSave_addsRecordToDatabase(): void
    {
        $account = Account::factory()->make(); // make not saved model
        $account->client_id = 99;

        $countBefore = Account::count();
        $this->repository->save($account);
        $countAfter = Account::count();

        self::assertEquals($countBefore + 1, $countAfter);
    }

    public function testSave_withModifiedProperty_updatesInDatabase(): void
    {
        $account = Account::factory()->make(); // make not saved model
        $account->client_id = 99;
        $account->save();

        $amountBefore = $account->amount;
        $account->amount = 78327328;
        $this->repository->save($account);

        $account->refresh();

        self::assertNotEquals($amountBefore, $account->amount);
    }
}
