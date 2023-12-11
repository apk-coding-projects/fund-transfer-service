<?php

declare(strict_types=1);

namespace Tests\Unit\RateImport;

use PHPUnit\Framework\TestCase;
use src\CurrencyRates\RateImport\Clients\ExchangerateClient;

class ExchangerateClientTest extends TestCase
{
    private ExchangerateClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new ExchangerateClient();
    }

    public function testGetBaseUrl_withPath_returnsCorrectUrl(): void
    {
        $url = $this->client->buildUrl('historical');

        self::assertEquals('http://api.exchangerate.host/historical', $url);
    }

    public function testGetBaseUrl_withEmptyPath_returnsCorrectUrl(): void
    {
        $url = $this->client->buildUrl();

        self::assertEquals('http://api.exchangerate.host/', $url);
    }
}
