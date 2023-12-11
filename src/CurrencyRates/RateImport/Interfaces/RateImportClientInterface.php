<?php

namespace src\CurrencyRates\RateImport\Interfaces;

use src\CurrencyRates\RateImport\Structures\BaseRateImportResponse;

interface RateImportClientInterface
{
    public function getRates(string $date, string $sourceCurrency, array $targetCurrency): BaseRateImportResponse;

    public function buildUrl(string $path = ''): string;
}
