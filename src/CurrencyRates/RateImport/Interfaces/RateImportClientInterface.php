<?php

namespace src\CurrencyRates\RateImport\Interfaces;

use src\CurrencyRates\RateImport\Structures\BaseRateImportRequest;
use src\CurrencyRates\RateImport\Structures\BaseRateImportResponse;

interface RateImportClientInterface
{
    public function getRates(BaseRateImportRequest $request): BaseRateImportResponse;

//    public function getJsonResponseBody();
//
//    public function getClient();
}
