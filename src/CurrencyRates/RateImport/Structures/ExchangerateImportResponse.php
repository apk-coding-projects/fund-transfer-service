<?php

declare(strict_types=1);

namespace src\CurrencyRates\RateImport\Structures;

class ExchangerateImportResponse extends BaseRateImportResponse
{
    public const KEY_SUCCESS = 'success';
    public const KEY_QUOTES = 'quotes';

    public function __construct(
        public bool $isSuccess,
        public array $responseArray,
        public string $date,
        public string $source,
        public array $rates,
    ) {
        $this->rates = $this->formatRates($this->rates);
    }

    public function formatRates(array $importedRates): array
    {
        $rates = [];

        // response format is [EURUSD => 1.123, ...]
        foreach ($importedRates as $currencies => $rate) {
            $targetCurrency = substr($currencies, -3);
            $rates[$targetCurrency] = $rate;
        }

        return $rates;
    }
}
