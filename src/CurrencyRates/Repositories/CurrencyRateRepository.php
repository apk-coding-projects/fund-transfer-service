<?php

declare(strict_types=1);

namespace src\CurrencyRates\Repositories;

use src\CurrencyRates\Models\CurrencyRate;

class CurrencyRateRepository
{
    public function batchInsert(array $data): void
    {
        CurrencyRate::insert($data);
    }

    public function getByDate(string $from, string $to, string $date): ?CurrencyRate
    {
        return CurrencyRate::where('from', $from)->where('to', $to)->where('date', $date)->first();
    }

    /** Get latest possible rate by date. If rate is not saved for searched date, it will return closest to that date */
    public function getLastRateByDate(string $from, string $to, string $date): ?CurrencyRate
    {
        return CurrencyRate::where('from', $from)
            ->where('to', $to)
            ->where('date', '<=', $date)
            ->orderByDesc('id')
            ->first();
    }
}
