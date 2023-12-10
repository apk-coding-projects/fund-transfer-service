<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use src\Accounts\Repositories\AccountRepository;
use src\Transactions\Exceptions\AccountNotFoundException;
use src\Transactions\Services\FundTransferTransactionService;
use Throwable;

class TransactionController extends BaseController
{
    public function __construct(
        private readonly FundTransferTransactionService $service,
        private readonly AccountRepository $accountRepository,
    ) {
    }

    public function makeTransaction(Request $request, int $accountId): array
    {
        try {
            $senderAccountId = intval($request->input('senderAccountId', 0));
            $receiverAccountId = intval($request->input('receiverAccountId', 0));
            $amount = intval($request->input('amount', 0));
            $currency = intval($request->input('currency', ''));

            $senderAccount = $this->accountRepository->getById($senderAccountId);
            $receiverAccount = $this->accountRepository->getById($receiverAccountId);
            if (!$senderAccount || !$receiverAccount) {
                throw new AccountNotFoundException('Receiver or Sender Account is not found!');
            }

            if (!$transactions) {
                return ResponseHelper::success(true, "No transactions found for account with ID: $accountId");
            }

            $payload = array_map(fn(Transaction $transaction) => $transaction->toArray(), $transactions);

            return ResponseHelper::success(true, payload: $payload);
        } catch (Throwable $t) {
            return ResponseHelper::failure(false, 'Something went wrong! Please try again.');
        }
    }
}
