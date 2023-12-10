<?php

declare(strict_types=1);

namespace src\Accounts\Services;

use src\Accounts\Models\Account;
use src\Accounts\Repositories\AccountRepository;
use src\Transactions\Exceptions\NegativeAmountException;
use src\Transactions\Exceptions\NotEnoughBalanceException;

class AccountService
{
    public function __construct(private readonly AccountRepository $repository) { }

    /**
     * @throws NegativeAmountException
     */
    public function addToAccount(Account $account, float $amount): void
    {
        if ($amount < 0.00) {
            throw new NegativeAmountException('Cannot add negative amount to account: ' . $amount);
        }

        $account->amount += $amount;

        $this->repository->save($account);
    }

    /**
     * @throws NegativeAmountException
     * @throws NotEnoughBalanceException
     */
    public function subtractFromAccount(Account $account, float $amount): void
    {
        if ($amount < 0.00) {
            throw new NegativeAmountException('Cannot add negative amount to account: ' . $amount);
        }

        if ($amount > $account->amount) {
            throw new NotEnoughBalanceException('Not enough balance in the account!');
        }

        $account->amount -= $amount;

        $this->repository->save($account);
    }
}
