<?php

declare(strict_types=1);

namespace Tests\Feature\RateImport\Helpers;

use src\CurrencyRates\Models\CurrencyRate;

class CurrencyRateHelper
{
    public static function build(string $from, string $to, string $date, float $rateValue): CurrencyRate
    {
        $rate = new CurrencyRate();
        $rate->from = $from;
        $rate->to = $to;
        $rate->date = $date;
        $rate->rate = $rateValue;

        return $rate;
    }
}
