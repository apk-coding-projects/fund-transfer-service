<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use src\CurrencyRates\Services\CurrencyConversionService;
use src\Transactions\Exceptions\TransactionException;
use src\Transactions\Factories\FundTransferRequestFactory;
use src\Transactions\Services\FundTransferTransactionService;
use Throwable;

class TransactionController extends BaseController
{
    public function __construct(
        private readonly FundTransferTransactionService $transactionService,
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

            $this->transactionService->transfer($fundTransferRequest);

            return ResponseHelper::success(true, 'Funds transferred successfully!');
        } catch (TransactionException $t) {
            return ResponseHelper::failure($t->getMessage()); // safe to error return message, we set it by ourselves
        } catch (Throwable $t) {
            return ResponseHelper::failure('Something went wrong! Please try again later.');
        }
    }
}
