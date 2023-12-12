<?php

declare(strict_types=1);

namespace src\CurrencyRates\RateImport\Services;

use Illuminate\Support\Facades\Log;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\RateImport\Clients\ExchangerateClient;
use src\CurrencyRates\RateImport\Structures\ExchangerateImportResponse;
use src\CurrencyRates\Repositories\CurrencyRateRepository;
use src\CurrencyRates\Structures\Currency;
use Throwable;

class CurrencyRateImportService
{
    private const MIN_CURRENCY_COUNT_TO_IMPORT = 2;

    public function __construct(
        private readonly ExchangerateClient $client,
        private readonly CurrencyRateRepository $rateRepository,
    ) {
    }

    public function import(array $currenciesToImport = Currency::SUPPORTED_CURRENCIES, ?string $date = null): void
    {
        $date = $date ?: date(ExchangerateClient::DATE_FORMAT);
        Log::info("Import started for $date, currencies:" . join(',', $currenciesToImport));

        if (count($currenciesToImport) < self::MIN_CURRENCY_COUNT_TO_IMPORT) {
            Log::info("Import ended for $date - cannot import less than two currencies");

            return; // not possible to import 0 currencies OR 1, for example, USD to USD, its always equals to 1
        }

        /*
         * ExchangerateClient accepts only one source currency, but multiple currencies to get rates to
         * Therefore we have to request for each source currency separately (potential N request)
         */
        foreach ($currenciesToImport as $currencyFrom) {
            try {
                $currenciesTo = array_diff($currenciesToImport, [$currencyFrom]);

                $response = $this->client->getRates($date, $currencyFrom, $currenciesTo);
                if (!$response->isSuccess) {
                    continue; // Something went wrong, results are not returned
                }

                $this->bulkSaveRecords($response);
            } catch (Throwable $e) {
                Log::error($e->getTraceAsString());
                continue;
            }
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
