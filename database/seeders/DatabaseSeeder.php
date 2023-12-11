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
        /**
         * Seed currency rates
         *
         * @var CurrencyRateImportService $service
         */
        $service = app(CurrencyRateImportService::class);
        $today = new DateTime(); // Today's date
        $service->import(date: $today->format('Y-m-d'));

        for ($i = 0; $i < 15; $i++) {
            $date = $today->modify('-1 day')->format('Y-m-d');
            $service->import(date: $date);
        }

        Client::factory()
            ->count(15)
            ->create();

        Client::factory()
            ->count(50)
            ->has(Account::factory()->count(rand(1, 5)))
            ->create();

        Transaction::factory()
            ->count(5000)
            ->create();
    }
}
