<?php

declare(strict_types=1);

namespace src\CurrencyRates\RateImport\Clients;

use Illuminate\Support\Facades\Http;
use src\CurrencyRates\RateImport\Interfaces\RateImportClientInterface;
use src\CurrencyRates\RateImport\Structures\BaseRateImportRequest;
use src\CurrencyRates\RateImport\Structures\BaseRateImportResponse;
use src\CurrencyRates\RateImport\Structures\ExchangerateImportResponse;

class ExchangerateClient implements RateImportClientInterface
{
    public function getRates(BaseRateImportRequest $request): BaseRateImportResponse
    {
        /**
         * 1) crate request curl with params
         * 2) make request
         * 3) parse data
         */

        Http::get('https://api.exchangerate.host/historical?access_key=' . env('EXCHANGERATE_API_KEY') . '& date = 2023-12-01');

        return new ExchangerateImportResponse();
    }

    public function getClient()
    {
//        curl_init('https://api.exchangerate.host/historical?access_key='.$access_key.'');
    }
}
