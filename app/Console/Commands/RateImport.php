<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use src\CurrencyRates\RateImport\Services\CurrencyRateImportService;

class RateImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excahngerates:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import currency rates from exchangerate.host';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /**
         * @var CurrencyRateImportService $service
         */
        $service = app(CurrencyRateImportService::class);
        $service->import();
    }
}
