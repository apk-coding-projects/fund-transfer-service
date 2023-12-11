<?php

declare(strict_types=1);

namespace Tests\Feature\RateImport\Factories;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use src\Accounts\Models\Account;
use src\Transactions\Exceptions\AccountNotFoundException;
use src\Transactions\Factories\FundTransferRequestFactory;
use Tests\TestCase;

class FundTransferRequestFactoryTest extends TestCase
{
    use DatabaseMigrations;

    private FundTransferRequestFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = app(FundTransferRequestFactory::class);
    }

    public function testBuild_withValidData_buildsFundTransferRequest(): void
    {
        $sender = $this->buildAccount(true);
        $receiver = $this->buildAccount(true);

        $request = $this->factory->build($sender->id, $receiver->id, 'USD', 100);

        self::assertEquals($sender->id, $request->senderAccount->id);
        self::assertEquals($receiver->id, $request->receiverAccount->id);
        self::assertEquals('USD', $request->currency);
        self::assertEquals(100, $request->amount);
    }

    /** @dataProvider missingDatabaseAccountsProvider */
    public function testBuild_withAccountsNotFound_throwsException(
        bool $isSenderPersisted,
        bool $isReceiverPersisted,
    ): void {
        $sender = $this->buildAccount($isSenderPersisted);
        $receiver = $this->buildAccount($isReceiverPersisted);

        self::expectException(AccountNotFoundException::class);
        self::expectExceptionMessage('Receiver or Sender Account is not found!');

        $this->factory->build($sender?->id ?? -1, $receiver?->id ?? -1, 'USD', 100);
    }

    public static function missingDatabaseAccountsProvider(): array
    {
        /**
         * 1) is sender persisted in DB
         * 1) is receiver persisted in DB
         */
        return [
            'missing_both_accounts_in_DB' => [false, false],
            'missing_sender_accounts_in_DB' => [false, true],
            'missing_receiver_accounts_in_DB' => [true, false],
        ];
    }

    private function buildAccount(bool $shouldSave): Account
    {
        $account = new Account();
        $account->currency = 'USD';
        $account->amount = 123;
        $account->client_id = 999;

        if (!$shouldSave) {
            return $account;
        }

        $account->save();

        return $account->refresh();
    }
}
