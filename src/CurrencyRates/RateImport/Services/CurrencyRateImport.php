<?php

declare(strict_types=1);

namespace src\CurrencyRates\RateImport\Services;

use Illuminate\Support\Facades\Log;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\RateImport\Clients\ExchangerateClient;
use src\CurrencyRates\RateImport\Structures\ExchangerateImportResponse;
use src\CurrencyRates\Repositories\CurrencyRateRepository;

class CurrencyRateImport
{
    public function __construct(
        private readonly ExchangerateClient $client,
        private readonly CurrencyRateRepository $rateRepository,
    )
    {
    }

    public function import(array $currenciesToImport = CurrencyRate::SUPPORTED_CURRENCIES, ?string $date = null): void
    {
        $date = $date ?: date(ExchangerateClient::DATE_FORMAT);

        Log::info("Import started for $date, currencies:" . join(',', $currenciesToImport));

        /*
         * ExchangerateClient accepts only one source currency, but multiple currencies to get rates to
         * Therefore we have to request for each source currency separately (potential N request)
         */
        foreach ($currenciesToImport as $currencyFrom) {
            $currenciesTo = array_diff($currenciesToImport, [$currencyFrom]);

            $response = $this->client->getRates($date, $currencyFrom, $currenciesTo);

            if (!$response->isSuccess) {
                continue; // Something went wrong, results are not returned
            }

            $this->bulkSaveRecords($response);
        }

        Log::info("Import ended for $date");
    }

    private function bulkSaveRecords(ExchangerateImportResponse $response): void
    {
        $dataToInsert = $this->prepareData($response);

        $this->rateRepository->batchInsert($dataToInsert);
    }

    private function prepareData(ExchangerateImportResponse $response): array
    {
        $currencyFrom = $response->source;
        $processedDate = $response->date;
        $now = date('Y-m-d H:i:s');
        $data = [];

        foreach ($response->rates as $currency => $rate) {
            $data[] = [
                'from' => $currencyFrom,
                'to' => $currency,
                'rate' => $rate,
                'date' => $processedDate,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $data;
    }
}
