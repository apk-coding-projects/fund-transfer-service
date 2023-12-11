<?php

declare(strict_types=1);

namespace src\CurrencyRates\RateImport\Clients;

use Illuminate\Support\Facades\Http;
use src\CurrencyRates\RateImport\Interfaces\RateImportClientInterface;
use src\CurrencyRates\RateImport\Structures\BaseRateImportResponse;
use src\CurrencyRates\RateImport\Structures\ExchangerateImportResponse;

class ExchangerateClient implements RateImportClientInterface
{
    public const DATE_FORMAT = 'Y-m-d';

    /** @return ExchangerateImportResponse */
    public function getRates(string $date, string $sourceCurrency, array $targetCurrency): BaseRateImportResponse
    {
        $response = Http::get($this->buildUrl('historical'), [
            'access_key' => env('EXCHANGERATE_API_KEY'),
            'date' => $date,
            'source' => $sourceCurrency,
            'currencies' => join(',', $targetCurrency),
        ]);

        $responseArray = $response->json();

        return new ExchangerateImportResponse(
            $responseArray[ExchangerateImportResponse::KEY_SUCCESS] ?? false,
            $responseArray,
            $date,
            $sourceCurrency,
            $responseArray[ExchangerateImportResponse::KEY_QUOTES] ?? []
        );
    }

    public function buildUrl(string $path = ''): string
    {
        return 'http://api.exchangerate.host/' . $path;
    }
}
