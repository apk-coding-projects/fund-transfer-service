<?php

declare(strict_types=1);

namespace src\CurrencyRates\Factories;

use src\CurrencyRates\Models\CurrencyRate;

class CurrencyRateFactory
{
    public function build(string $from, string $to, string $date, float $rateValue): CurrencyRate
    {
        $rate = new CurrencyRate();
        $rate->from = $from;
        $rate->to = $to;
        $rate->date = $date;
        $rate->rate = $rateValue;

        return $rate;
    }
}
