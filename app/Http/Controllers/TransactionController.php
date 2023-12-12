<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use src\Transactions\Exceptions\TransactionException;
use src\Transactions\Factories\FundTransferRequestFactory;
use src\Transactions\Services\TransactionService;
use Throwable;

class TransactionController extends BaseController
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly FundTransferRequestFactory $requestFactory,
    ) {
    }

    public function makeTransaction(Request $request): array
    {
        try {
            $senderId = intval($request->input('senderAccountId', 0));
            $receiverId = intval($request->input('receiverAccountId', 0));
            $amount = floatval($request->input('amount', 0));
            $currency = strval($request->input('currency', ''));

            $fundTransferRequest = $this->requestFactory->build($senderId, $receiverId, $currency, $amount);

            $this->transactionService->makeTransaction($fundTransferRequest);

            return ResponseHelper::success(true, 'Funds transferred successfully!');
        } catch (TransactionException $t) {
            Log::error($t->getTraceAsString());

            // safe to return error message, we set it by ourselves
            return ResponseHelper::failure($t->getMessage(), 200);
        } catch (Throwable $t) {
            Log::error($t->getTraceAsString());

            return ResponseHelper::failure('Something went wrong! Please try again later.');
        }
    }
}
