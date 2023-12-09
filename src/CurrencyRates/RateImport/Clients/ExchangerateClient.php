<?php

declare(strict_types=1);

namespace src\CurrencyRates\RateImport\Clients;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use src\Common\Helpers\RedisCacheHelper;
use src\CurrencyRates\RateImport\Interfaces\RateImportClientInterface;
use src\CurrencyRates\RateImport\Structures\BaseRateImportResponse;
use src\CurrencyRates\RateImport\Structures\ExchangerateImportResponse;

class ExchangerateClient implements RateImportClientInterface
{
    public const DATE_FORMAT = 'Y-m-d';

    /** @return ExchangerateImportResponse */
    public function getRates(string $date, string $sourceCurrency, array $targetCurrency): BaseRateImportResponse
    {
        //TODo delete
        $responseJson = RedisCacheHelper::get("testing_$date-$sourceCurrency");
        if ($responseJson) {
            $responseArray = json_decode($responseJson, true);

            return new ExchangerateImportResponse(
                $responseArray,
                $date,
                $sourceCurrency,
                $responseArray['quotes'] ?? []
            );
        }

        $response = Http::get($this->getBaseUrl('historical'), [
            'access_key' => env('EXCHANGERATE_API_KEY'),
            'date' => $date,
            'source' => $sourceCurrency,
            'currencies' => join(',', $targetCurrency),
        ]);

        $responseArray = $response->json();
        RedisCacheHelper::set("testing_$date-$sourceCurrency", json_encode($responseArray), 86400);//TODo delete

        return new ExchangerateImportResponse(
            $responseArray,
            $date,
            $sourceCurrency,
            $responseArray['quotes'] ?? []
        );
    }

    public function getBaseUrl(string $path = ''): string
    {
        return 'http://api.exchangerate.host/' . $path;
    }
}
