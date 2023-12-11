<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use src\Transactions\Models\Transaction;
use src\Transactions\Repositories\TransactionRepository;
use Throwable;

class AccountController extends BaseController
{
    public function __construct(private readonly TransactionRepository $transactionRepository) { }

    public function transactions(Request $request, int $accountId): array
    {
        try {
            $limit = intval($request->input('limit', 0));
            $offset = intval($request->input('offset', 0));

            $transactions = $this->transactionRepository->getPaginatedByAccountId($accountId, $limit, $offset);

            if (!$transactions) {
                return ResponseHelper::success(true, "No transactions found for account with ID: $accountId");
            }

            $payload = array_map(fn(Transaction $transaction) => $transaction->toArray(), $transactions);

            return ResponseHelper::success(true, payload: $payload);
        } catch (Throwable $t) {
            return ResponseHelper::failure('Something went wrong! Please try again.');
        }
    }
}
