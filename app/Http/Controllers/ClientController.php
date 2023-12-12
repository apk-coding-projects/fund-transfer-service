<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseHelper;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use src\Accounts\Models\Account;
use src\Accounts\Repositories\AccountRepository;
use Throwable;

class ClientController extends BaseController
{
    public function __construct(private readonly AccountRepository $accountRepository) { }

    public function accounts(int $clientId): array
    {
        try {
            $accounts = $this->accountRepository->getByClientId($clientId);
            if (!$accounts) {
                return ResponseHelper::success(true, "No accounts found for customer with ID: $clientId");
            }

            $payload = array_map(fn(Account $account) => $account->toArray(), $accounts);

            return ResponseHelper::success(true, payload: $payload);
        } catch (Throwable $t) {
            Log::error($t->getTraceAsString());

            return ResponseHelper::failure('Something went wrong! Please try again.');
        }
    }
}
