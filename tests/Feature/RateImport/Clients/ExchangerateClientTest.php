<?php

declare(strict_types=1);

namespace RateImport\Clients;

use Illuminate\Support\Facades\Http;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\RateImport\Clients\ExchangerateClient;
use Tests\Feature\RateImport\Helpers\RateImportHelper;
use Tests\TestCase;

class ExchangerateClientTest extends TestCase
{
    private ExchangerateClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new ExchangerateClient();
    }

    public function testGetRates_withUnsuccessfulResponse_returnsResponse(): void
    {
        $clientFakeResponse = RateImportHelper::getFakeFailureResponse();
        Http::fake(['http://api.exchangerate.host/*' => Http::response($clientFakeResponse)]);

        $response = $this->client->getRates(
            date('Y-m-d'),
            CurrencyRate::CURRENCY_USD,
            CurrencyRate::SUPPORTED_CURRENCIES
        );

        self::assertFalse($response->isSuccess);
        self::assertEmpty($response->rates);
        self::assertEquals(CurrencyRate::CURRENCY_USD, $response->source);
        self::assertEquals(date('Y-m-d'), $response->date);
    }

    public function testGetRates_withSuccessfulResponse_returnsResponse(): void
    {
        $clientFakeResponse = RateImportHelper::getFakeSuccessResponse();
        Http::fake(['http://api.exchangerate.host/*' => Http::response($clientFakeResponse)]);

        $response = $this->client->getRates(
            date('Y-m-d'),
            CurrencyRate::CURRENCY_USD,
            CurrencyRate::SUPPORTED_CURRENCIES
        );

        self::assertTrue($response->isSuccess);
        self::assertNotEmpty($response->rates);
        self::assertEmpty(array_diff(array_values($clientFakeResponse['quotes']), array_values($response->rates)));
        self::assertEquals(CurrencyRate::CURRENCY_USD, $response->source);
        self::assertEquals(date('Y-m-d'), $response->date);
    }
}
