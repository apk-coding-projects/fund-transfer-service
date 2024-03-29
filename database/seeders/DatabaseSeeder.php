<?php

namespace Database\Seeders;

use DateTime;
use Illuminate\Database\Seeder;
use src\Accounts\Models\Account;
use src\Clients\Models\Client;
use src\CurrencyRates\RateImport\Services\CurrencyRateImportService;
use src\Transactions\Models\Transaction;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    }
}
