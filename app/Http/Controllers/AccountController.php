<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use src\Transactions\Models\Transaction;
use src\Transactions\Repositories\TransactionRepository;
use Throwable;

class AccountController extends BaseController
{
    public function __construct(private readonly TransactionRepository $transactionRepository) { }

    public function transactions(Request $request, int $accountId): array
    {
        try {
            $limit = is_null($request->input('limit')) ? null : (intval($request->input('limit')));
            $offset = intval($request->input('offset', 0));

            if ($limit === 0) {
                return ResponseHelper::success(true, "To get transactions increase limit to be more than 0");
            }

            $transactions = $this->transactionRepository->getPaginatedByAccountId($accountId, $limit, $offset);
            if (!$transactions) {
                return ResponseHelper::success(true, "No transactions found for account with ID: $accountId");
            }

            $payload = array_map(fn(Transaction $transaction) => $transaction->toArray(), $transactions);

            return ResponseHelper::success(true, payload: $payload);
        } catch (Throwable $t) {
            Log::error($t->getTraceAsString());

            return ResponseHelper::failure('Something went wrong! Please try again.');
        }
    }
}
