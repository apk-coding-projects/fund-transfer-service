<?php

declare(strict_types=1);

namespace Tests\Feature\CurrencyRates\Repositories;

use DateTime;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use src\CurrencyRates\Repositories\CurrencyRateRepository;
use Tests\Feature\RateImport\Helpers\CurrencyRateHelper;
use Tests\TestCase;

class CurrencyRateRepositoryTest extends TestCase
{
    use DatabaseMigrations;

    private CurrencyRateRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = app(CurrencyRateRepository::class);
    }

    public function testGetByDate_withNoReteInDB_returnNull(): void
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = date('Y-m-d');

        $rate = $this->repo->getByDate($from, $to, $date);

        self::assertNull($rate);
    }

    public function testGetByDate_withOnlyPastRateInDb_returnNull(): void
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = (new DateTime())->modify('-2 day')->format('Y-m-d');
        $rateValue = 0.89;

        $currencyRate = CurrencyRateHelper::build($from, $to, $date, $rateValue);
        $currencyRate->save();

        $rate = $this->repo->getByDate($from, $to, date('Y-m-d'));

        self::assertNull($rate);
    }

    public function testGetByDate_withRateInDb_returnCorrect(): void
    {
        $from = 'EUR';
        $to = 'NZD';
        $date = date('Y-m-d');
        $rateValue = 0.89;

        $currencyRate = CurrencyRateHelper::build($from, $to, $date, $rateValue);
        $currencyRate->save();

        $rate = $this->repo->getByDate($from, $to, $date);

        self::assertEquals($to, $rate->to);
        self::assertEquals($from, $rate->from);
        self::assertEquals($date, $rate->date);
        self::assertEquals($rateValue, $rate->rate);
    }

    public function testGetLastByDate_withTodayRate_returnsCorrect(): void
    {
        $from = 'EUR';
        $to = 'NZD';
        $today = date('Y-m-d');
        $yesterday = (new DateTime())->modify('-2 day')->format('Y-m-d');
        $rateValue = 0.89;

        $currencyRate = CurrencyRateHelper::build($from, $to, $today, $rateValue);
        $currencyRate->save();
        $currencyRate = CurrencyRateHelper::build($from, $to, $yesterday, $rateValue);
        $currencyRate->save();

        $rate = $this->repo->getByDate($from, $to, $today);

        self::assertEquals($to, $rate->to);
        self::assertEquals($from, $rate->from);
        self::assertEquals($today, $rate->date);
        self::assertEquals($rateValue, $rate->rate);
    }

    public function testGetLastByDate_withYesterdayRateCheckToday_returnsCorrect(): void
    {
        $from = 'EUR';
        $to = 'NZD';
        $yesterday = (new DateTime())->modify('-2 day')->format('Y-m-d');
        $rateValue = 0.89;

        $currencyRate = CurrencyRateHelper::build($from, $to, $yesterday, $rateValue);
        $currencyRate->save();

        $rate = $this->repo->getByDate($from, $to, $yesterday);

        self::assertEquals($to, $rate->to);
        self::assertEquals($from, $rate->from);
        self::assertEquals($yesterday, $rate->date);
        self::assertEquals($rateValue, $rate->rate);
    }
}
