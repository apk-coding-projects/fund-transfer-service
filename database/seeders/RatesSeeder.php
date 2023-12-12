<?php

namespace Database\Seeders;

use DateTime;
use Illuminate\Database\Seeder;
use src\CurrencyRates\RateImport\Services\CurrencyRateImportService;

class RatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
    }
}
