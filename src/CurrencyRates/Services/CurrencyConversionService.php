<?php

declare(strict_types=1);

namespace src\CurrencyRates\Services;

use src\CurrencyRates\Exceptions\RateNotFoundException;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\RateImport\Clients\ExchangerateClient;
use src\CurrencyRates\Repositories\CurrencyRateRepository;

class CurrencyConversionService
{
    public function __construct(
        private readonly CurrencyRateRepository $currencyRepository,
        private readonly ExchangerateClient $client,
    ) {
    }

    /**  @throws RateNotFoundException */
    public function convert(string $from, string $to, string $date, float $amount): float
    {
        $rate = $this->getRate($from, $to, $date);

        return round($amount * $rate->rate, 2);
    }

    /**
     * 1) Try getting historical to escape 3rd party API calls
     * 2) Try calling an API to get live rate
     * 3) Try getting the closest date value from past. For example if 16-06-2020 is missing we return 15-06-2020
     *
     * @throws RateNotFoundException
     */
    public function getRate(string $from, string $to, string $date): CurrencyRate
    {
        $rate = $this->currencyRepository->getByDate($from, $to, $date);
        if ($rate?->rate) {
            return $rate;
        }

        $liveRate = $this->getLiveRate($date, $from, $to);
        if ($liveRate?->rate) {
            return $liveRate;
        }

        $historicalRate = $this->currencyRepository->getLastRateByDate($from, $to, $date);
        if ($historicalRate?->rate) {
            return $historicalRate;
        }

        /*
         * If rate is not found we cannot fall back to some AVG value.
         * Check conversions from USD to JPY, not the same as EUR to USD. There will be monetary impact
         */
        throw new RateNotFoundException("Could not find conversion rate from $from to $to currencies by date $date");
    }

    public function getLiveRate(string $date, string $from, string $to): ?CurrencyRate
    {
        $response = $this->client->getRates($date, $from, [$to]);

        if (!$response->isSuccess) {
            return null;
        }

        $rate = new CurrencyRate();
        $rate->from = $from;
        $rate->to = $to;
        $rate->date = $date;
        $rate->rate = $response->rates[0];

        return $rate;
    }
}
