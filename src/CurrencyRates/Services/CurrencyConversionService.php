<?php

declare(strict_types=1);

namespace src\CurrencyRates\Services;

use Illuminate\Support\Facades\Log;
use src\Common\Helpers\RedisCacheHelper;
use src\CurrencyRates\Exceptions\RateNotFoundException;
use src\CurrencyRates\Factories\CurrencyRateFactory;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\RateImport\Clients\ExchangerateClient;
use src\CurrencyRates\Repositories\CurrencyRateRepository;
use Throwable;

class CurrencyConversionService
{
    const CACHE_PREFIX_CURRENCY_RATE = 'CACHED_CURRENCY_RATES';
    const CACHE_DURATION_SECONDS = 30;

    public function __construct(
        private readonly CurrencyRateRepository $currencyRepository,
        private readonly ExchangerateClient $client,
        private readonly CurrencyRateFactory $rateFactory,
    ) {
    }

    /**  @throws RateNotFoundException */
    public function convert(string $from, string $to, string $date, float $amount): float
    {
        $rate = $this->getRate($from, $to, $date);

        return round($amount * $rate->rate, 2);
    }

    /**
     * 1) try getting from cache
     * 2) Try getting historical to escape 3rd party API calls
     * 3) Try calling an API to get live rate
     * 4) Try getting the closest date value from past. For example if 16-06-2020 is missing we return 15-06-2020
     *
     * @throws RateNotFoundException
     */
    public function getRate(string $from, string $to, string $date): CurrencyRate
    {
        $key = $this->getKey($from, $to, $date);
        $rateJson = RedisCacheHelper::get($key);
        if ($rateJson) {
            $rate = new CurrencyRate();
            $rate->fill(json_decode($rateJson, true));
            $rate->rate = round($rate->rate, 2);

            return $rate;
        }

        $rate = $this->currencyRepository->getByDate($from, $to, $date);
        if ($rate?->rate) {
            RedisCacheHelper::set($key, json_encode($rate->toArray()), self::CACHE_DURATION_SECONDS);

            return $rate;
        }

        $liveRate = $this->getLiveRate($date, $from, $to);
        if ($liveRate?->rate) {
            RedisCacheHelper::set($key, json_encode($liveRate->toArray()), self::CACHE_DURATION_SECONDS);

            return $liveRate;
        }

        $historicalRate = $this->currencyRepository->getLastRateByDate($from, $to, $date);
        if ($historicalRate?->rate) {
            RedisCacheHelper::set($key, json_encode($historicalRate->toArray()), self::CACHE_DURATION_SECONDS);

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
        if ($from === $to) {
            return $this->rateFactory->build($from, $to, $date, 1); // USD to USD is always 1
        }

        try {
            $response = $this->client->getRates($date, $from, [$to]);

            if (!$response->isSuccess) {
                return null;
            }

            $rateValue = $response->rates[array_key_first($response->rates)];

            return $this->rateFactory->build($from, $to, $date, $rateValue);
        } catch (Throwable $t) {
            Log::error($t->getTraceAsString());

            return null;
        }
    }

    private function getKey(string $from, string $to, string $date): string
    {
        return join('_', [self::CACHE_PREFIX_CURRENCY_RATE, $from, $to, $date]);
    }
}
