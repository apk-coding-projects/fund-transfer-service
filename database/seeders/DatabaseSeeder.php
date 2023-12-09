<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use src\Accounts\Models\Account;
use src\Clients\Models\Client;
use src\CurrencyRates\RateImport\Services\CurrencyRateImport;

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
         * @var CurrencyRateImport $service
         */
        //        $service = app(CurrencyRateImport::class);
        //        $service->import();

        Client::factory()
            ->count(2)
            ->has(Account::factory()->count(rand(0, 4)))
            ->create();
    }
}
