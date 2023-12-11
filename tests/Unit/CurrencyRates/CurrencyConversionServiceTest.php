<?php

declare(strict_types=1);

namespace Tests\Unit\CurrencyRates;

use Exception;
use Mockery;
use src\CurrencyRates\Factories\CurrencyRateFactory;
use src\CurrencyRates\RateImport\Clients\ExchangerateClient;
use src\CurrencyRates\Repositories\CurrencyRateRepository;
use src\CurrencyRates\Services\CurrencyConversionService;
use Tests\TestCase;

class CurrencyConversionServiceTest extends TestCase
{
    private CurrencyRateRepository $mockedCurrencyRepository;
    private ExchangerateClient $mockedClient;
    private CurrencyRateFactory $mockedRateFactory;

    private CurrencyConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedCurrencyRepository = Mockery::mock(CurrencyRateRepository::class);
        $this->mockedClient = Mockery::mock(ExchangerateClient::class);
        $this->mockedRateFactory = Mockery::mock(CurrencyRateFactory::class);

        $this->service = new CurrencyConversionService(
            $this->mockedCurrencyRepository,
            $this->mockedClient,
            $this->mockedRateFactory,
        );
    }

    public function testGetLiveRate_withClientException_returnsNull(): void
    {
        $date = date('Y-m-d');
        $this->mockedClient->expects('getRates')->withArgs([$date, 'USD', ['AUD']])->andThrows(Exception::class);

        $rate = $this->service->getLiveRate($date, 'USD', 'AUD');

        self::assertNull($rate);
    }
}
