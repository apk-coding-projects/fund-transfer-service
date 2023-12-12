<?php

declare(strict_types=1);

namespace Tests\Unit\RateImport;

use Exception;
use Mockery;
use src\CurrencyRates\Models\CurrencyRate;
use src\CurrencyRates\RateImport\Clients\ExchangerateClient;
use src\CurrencyRates\RateImport\Services\CurrencyRateImportService;
use src\CurrencyRates\RateImport\Structures\ExchangerateImportResponse;
use src\CurrencyRates\Repositories\CurrencyRateRepository;
use src\CurrencyRates\Structures\Currency;
use Tests\TestCase;

class CurrencyRateImportServiceTest extends TestCase
{
    private ExchangerateClient $mockedClient;
    private CurrencyRateRepository $mockedRepo;

    private CurrencyRateImportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedClient = Mockery::mock(ExchangerateClient::class);
        $this->mockedRepo = Mockery::mock(CurrencyRateRepository::class);

        $this->service = new CurrencyRateImportService($this->mockedClient, $this->mockedRepo);
    }

    public function testImport_withNotEnoughImportCurrencies_returnsEarly(): void
    {
        $this->service->import([Currency::CURRENCY_USD]);

        $this->mockedClient->shouldNotHaveBeenCalled();
        $this->mockedRepo->shouldNotHaveBeenCalled();
    }

    public function testImport_withTwoRatesToImport_savesTwo(): void
    {
        $from = Currency::CURRENCY_USD;
        $to = Currency::CURRENCY_EUR;

        $this->mockedClient->expects('getRates')
            ->twice()
            ->andReturn($this->getExchangerateImportResponse(true, $from, $to));
        $this->mockedRepo->expects('batchInsert')->twice();

        $this->service->import([$from, $to]);
    }

    public function testImport_withOneRatesFailing_savesOne(): void
    {
        $from = Currency::CURRENCY_USD;
        $to = Currency::CURRENCY_EUR;

        $this->mockedClient->expects('getRates')
            ->withArgs([date('Y-m-d'), $from, [1 => $to]])
            ->andReturn($this->getExchangerateImportResponse(true, $from, $to));
        $this->mockedClient->expects('getRates')
            ->withArgs([date('Y-m-d'), $to, [0 => $from]])
            ->andThrow(Exception::class);

        $this->mockedRepo->expects('batchInsert')->once();

        $this->service->import([$from, $to]);
    }

    private function getExchangerateImportResponse(bool $isSuccess, string $from, string $to)
    {
        return new ExchangerateImportResponse($isSuccess, [], date('Y-m-d'), $from, ["$from$to" => 0.95]);
    }
}
