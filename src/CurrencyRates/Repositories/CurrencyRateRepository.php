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
}
