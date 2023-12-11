<?php

declare(strict_types=1);

namespace Tests\Feature\Transactions\Repositories;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use src\Transactions\Repositories\TransactionRepository;
use Tests\Feature\Transactions\Helpers\TransactionHelper;
use Tests\TestCase;

class TransactionRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    private const ACCOUNT_ID = 12345;

    private TransactionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // prepare data
        for ($i = 0; $i < 30; $i++) {
            $transaction = TransactionHelper::build(self::ACCOUNT_ID);
            $transaction->save();
        }

        $this->repository = app(TransactionRepository::class);
    }

    public function testGetPaginatedByAccountId_withoutPaginationSpecified_returnsAll(): void
    {
        $transactions = $this->repository->getPaginatedByAccountId(self::ACCOUNT_ID);

        self::assertEquals(30, count($transactions));
    }

    public function testGetPaginatedByAccountId_withPagination_returnsCorrectData(): void
    {
        $transactions = $this->repository->getPaginatedByAccountId(self::ACCOUNT_ID, 5, 10);

        self::assertEquals(5, count($transactions));
    }
}
