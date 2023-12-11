<?php

declare(strict_types=1);

namespace RateImport\Services;

use Illuminate\Support\Facades\Http;
use src\CurrencyRates\Services\CurrencyConversionService;
use Tests\Feature\RateImport\Helpers\RateImportHelper;
use Tests\TestCase;

class CurrencyConversionServiceTest extends TestCase
{
    private const HTTP_API_EXCHANGERATE_URL = 'http://api.exchangerate.host/*';

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
}
