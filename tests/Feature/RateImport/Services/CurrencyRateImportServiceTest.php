<?php

declare(strict_types=1);

namespace Tests\Feature\RateImport\Services;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\RateImport\Services\CurrencyRateImportService;
use src\CurrencyRates\Structures\Currency;
use Tests\Feature\RateImport\Helpers\RateImportHelper;
use Tests\TestCase;

class CurrencyRateImportServiceTest extends TestCase
{
    use DatabaseMigrations;

    private const HTTP_API_EXCHANGERATE_URL = 'http://api.exchangerate.host/*';

    private CurrencyRateImportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CurrencyRateImportService::class);
    }

    /**
     * @dataProvider invalidCurrencyCountProvider
     */
    public function testImport_withInvalidCurrencyCounts_ratesNotSaved(array $currenciesToImport): void
    {
        $before = CurrencyRate::count();

        $this->service->import($currenciesToImport);

        $after = CurrencyRate::count();

        self::assertEquals($before, $after);
    }

    public function testImport_withSupportedCurrencies_ratesSaved(): void
    {
        foreach (Currency::SUPPORTED_CURRENCIES as $currency) {
            Http::fake(
                [
                    self::HTTP_API_EXCHANGERATE_URL => Http::response(
                        RateImportHelper::getFakeSuccessResponse($currency)
                    ),
                ]
            );
        }

        $before = CurrencyRate::count();
        $this->service->import();
        $after = CurrencyRate::count();

        self::assertNotEquals($before, $after);
        self::assertEquals(30, $after);
    }

    public function testImport_withAllCurrencyRatesFailing_noRatesSaved(): void
    {
        // NZD import fails, but others are successful
        Http::fake([self::HTTP_API_EXCHANGERATE_URL => Http::response(RateImportHelper::getFakeFailureResponse())]);

        $before = CurrencyRate::count();
        $this->service->import();
        $after = CurrencyRate::count();

        self::assertEquals($before, $after);
        self::assertEquals(0, $after);
    }

    public static function invalidCurrencyCountProvider(): array
    {
        return [
            ['no_currencies' => []],
            ['only_one_currency' => [Currency::CURRENCY_USD]],
        ];
    }
}
