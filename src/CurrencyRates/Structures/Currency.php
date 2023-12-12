<?php

declare(strict_types=1);

namespace src\CurrencyRates\Structures;

class Currency
{
    public const SUPPORTED_CURRENCIES = [
        self::CURRENCY_USD,
        self::CURRENCY_GBP,
        self::CURRENCY_EUR,
        self::CURRENCY_AUD,
        self::CURRENCY_NZD,
        self::CURRENCY_CAD,
    ];

    public const CURRENCY_USD = 'USD';
    public const CURRENCY_CAD = 'CAD';
    public const CURRENCY_EUR = 'EUR';
    public const CURRENCY_NZD = 'NZD';
    public const CURRENCY_GBP = 'GBP';
    public const CURRENCY_AUD = 'AUD';
}
