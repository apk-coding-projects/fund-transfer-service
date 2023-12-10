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

    public function getLastRateByDate(string $from, string $to, string $date): ?CurrencyRate
    {
        return CurrencyRate::where('from', $from)
            ->where('to', $to)
            ->where('date', '<=', $date)
            ->orderByDesc('id')
            ->first();
    }
}
