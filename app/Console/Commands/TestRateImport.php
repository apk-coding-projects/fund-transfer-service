<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use src\CurrencyRates\RateImport\Clients\ExchangerateClient;
use src\CurrencyRates\RateImport\Structures\ExchangerateImportRequest;

class TestRateImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /**
         * @var ExchangerateClient $service
         */
        $service = app(ExchangerateClient::class);
        $request = new ExchangerateImportRequest();
        var_dump(43);
//        $service->getRates($request);
    }
}
