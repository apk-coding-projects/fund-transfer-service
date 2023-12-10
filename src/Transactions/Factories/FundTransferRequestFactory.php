<?php

declare(strict_types=1);

namespace src\Transactions\Factories;

use src\Accounts\Repositories\AccountRepository;
use src\Transactions\Exceptions\AccountNotFoundException;
use src\Transactions\Structures\FundTransferRequest;

class FundTransferRequestFactory
{
    public function __construct(private readonly AccountRepository $accountRepository) { }

    /**  @throws AccountNotFoundException */
    public function build(
        int $senderAccountId,
        int $receiverAccountId,
        string $currency,
        float $amount,
    ): FundTransferRequest {
        $senderAccount = $this->accountRepository->getById($senderAccountId);
        $receiverAccount = $this->accountRepository->getById($receiverAccountId);
        if (!$senderAccount || !$receiverAccount) {
            throw new AccountNotFoundException('Receiver or Sender Account is not found!');
        }

        return new FundTransferRequest($amount, $currency, $senderAccount, $receiverAccount);
    }
}
