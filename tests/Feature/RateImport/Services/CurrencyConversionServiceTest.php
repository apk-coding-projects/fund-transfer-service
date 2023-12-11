<?php

declare(strict_types=1);

namespace Tests\Feature\RateImport\Services;

use DateTime;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use src\CurrencyRates\Exceptions\RateNotFoundException;
use src\CurrencyRates\Services\CurrencyConversionService;
use Tests\Feature\RateImport\Helpers\CurrencyRateHelper;
use Tests\Feature\RateImport\Helpers\RateImportHelper;
use Tests\TestCase;

class CurrencyConversionServiceTest extends TestCase
{
    use DatabaseMigrations;

    private const HTTP_API_EXCHANGERATE_URL = 'http://api.exchangerate.host/*';
    private const CACHE_PREFIX_CURRENCY_RATE = 'CACHED_CURRENCY_RATES';

    private CurrencyConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CurrencyConversionService::class);
    }

    public function testGetLiveRate_withClientError_returnsNull()
    {
        Http::fake([self::HTTP_API_EXCHANGERATE_URL => Http::response(RateImportHelper::getFakeFailureResponse()),]);

        $rate = $this->service->getLiveRate(date('Y-m-d'), 'USD', 'EUR');

        self::assertNull($rate);
    }

    public function testGetLiveRate_withRateFetched_returnsCorrectCurrencyRate()
    {
        $from = 'EUR';
        $to = 'NZD';
        $rateValue = 0.895;

        $response = RateImportHelper::getOneRateSuccessResponse($from, $to, $rateValue);
        Http::fake(
            [self::HTTP_API_EXCHANGERATE_URL => Http::response($response)]
        );

        $rate = $this->service->getLiveRate(date('Y-m-d'), $from, $to);

        self::assertNotNull($rate);
        self::assertEquals(date('Y-m-d'), $rate->date);
        self::assertEquals($from, $rate->from);
        self::assertEquals($to, $rate->to);
        self::assertEquals($rateValue, $rate->rate);
    }

    public function testGetRate_withCachedRate_returnsCachedRate()
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = date('Y-m-d');
        $rateValue = 0.89;

        $currencyRate = CurrencyRateHelper::build($from, $to, $date, $rateValue);

        Redis::shouldReceive('get')
            ->once()
            ->with($this->getCacheKey($from, $to, $date))
            ->andReturn(json_encode($currencyRate->toArray()));

        $calculatedRate = $this->service->getRate($from, $to, $date);

        self::assertEquals($currencyRate->from, $calculatedRate->from);
        self::assertEquals($currencyRate->to, $calculatedRate->to);
        self::assertEquals($currencyRate->date, $calculatedRate->date);
        self::assertEquals($currencyRate->rate, $calculatedRate->rate);
    }

    public function testGetRate_withNoCachedRate_returnsDbRate()
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = date('Y-m-d');
        $rateValue = 0.89;

        $currencyRate = CurrencyRateHelper::build($from, $to, $date, $rateValue);
        $currencyRate->save();

        Redis::shouldReceive('get')
            ->once()
            ->with($this->getCacheKey($from, $to, $date))
            ->andReturn(null);

        Redis::shouldReceive('setex')
            ->once();

        $calculatedRate = $this->service->getRate($from, $to, $date);

        self::assertEquals($currencyRate->from, $calculatedRate->from);
        self::assertEquals($currencyRate->to, $calculatedRate->to);
        self::assertEquals($currencyRate->date, $calculatedRate->date);
        self::assertEquals($currencyRate->rate, $calculatedRate->rate);
    }

    public function testGetRate_withNoCacheAndDatabaseRate_returnsLiveRate()
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = date('Y-m-d');
        $rateValue = 0.89;

        $response = RateImportHelper::getOneRateSuccessResponse($from, $to, $rateValue);
        Http::fake(
            [self::HTTP_API_EXCHANGERATE_URL => Http::response($response)]
        );

        Redis::shouldReceive('get')
            ->once()
            ->with($this->getCacheKey($from, $to, $date))
            ->andReturn(null);

        Redis::shouldReceive('setex')
            ->once();

        $calculatedRate = $this->service->getRate($from, $to, $date);

        self::assertEquals($from, $calculatedRate->from);
        self::assertEquals($to, $calculatedRate->to);
        self::assertEquals($date, $calculatedRate->date);
        self::assertEquals($rateValue, $calculatedRate->rate);
    }

    public function testGetRate_withNoCacheAndDatabaseAndLiveRate_returnsHistoricalRate()
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = date('Y-m-d');
        $rateValue = 0.89;

        $twoDaysAgo = (new DateTime())->modify('-1 day')->format('Y-m-d');
        $currencyRate = CurrencyRateHelper::build($from, $to, $twoDaysAgo, $rateValue);
        $currencyRate->save();

        Http::fake([self::HTTP_API_EXCHANGERATE_URL => Http::response(RateImportHelper::getFakeFailureResponse())]);

        Redis::shouldReceive('get')
            ->once()
            ->with($this->getCacheKey($from, $to, $date))
            ->andReturn(null);

        Redis::shouldReceive('setex')
            ->once();

        $calculatedRate = $this->service->getRate($from, $to, $date);

        self::assertEquals($from, $calculatedRate->from);
        self::assertEquals($to, $calculatedRate->to);
        self::assertEquals($twoDaysAgo, $calculatedRate->date);
        self::assertEquals($rateValue, $calculatedRate->rate);
    }

    public function testGetRate_withNoRatesAvailable_throwsException()
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = date('Y-m-d');

        Http::fake([self::HTTP_API_EXCHANGERATE_URL => Http::response(RateImportHelper::getFakeFailureResponse())]);

        Redis::shouldReceive('get')
            ->once()
            ->with($this->getCacheKey($from, $to, $date))
            ->andReturn(null);

        self::expectException(RateNotFoundException::class);
        self::expectExceptionMessage("Could not find conversion rate from $from to $to currencies by date $date");

        $this->service->getRate($from, $to, $date);
    }

    public function testConvert_withNoRateFound_throwsException(): void
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = date('Y-m-d');

        Http::fake([self::HTTP_API_EXCHANGERATE_URL => Http::response(RateImportHelper::getFakeFailureResponse())]);

        Redis::shouldReceive('get')
            ->once()
            ->with($this->getCacheKey($from, $to, $date))
            ->andReturn(null);

        self::expectException(RateNotFoundException::class);
        self::expectExceptionMessage("Could not find conversion rate from $from to $to currencies by date $date");

        $this->service->convert($from, $to, $date, 100);
    }

    public function testConvert_withRateFromCache_convertsCorrectly(): void
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = date('Y-m-d');
        $rateValue = 0.89;

        $currencyRate = CurrencyRateHelper::build($from, $to, $date, $rateValue);

        Redis::shouldReceive('get')
            ->once()
            ->with($this->getCacheKey($from, $to, $date))
            ->andReturn(json_encode($currencyRate->toArray()));

        $convertedAmount = $this->service->convert($from, $to, $date, 100);

        self::assertIsFloat($convertedAmount);
        self::assertEquals(round(100 * $rateValue, 2), $convertedAmount);
    }

    private function getCacheKey(string $from, string $to, string $date): string
    {
        return join('_', [self::CACHE_PREFIX_CURRENCY_RATE, $from, $to, $date]);
    }
}
