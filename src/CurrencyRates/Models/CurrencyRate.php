<?php

namespace src\CurrencyRates\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $from
 * @property string $to
 * @property float $rate
 * @property string $date
 * @property string $created_at
 * @property string $updated_at
 */
class CurrencyRate extends Model
{
    public const CURRENCY_USD = 'USD';
    public const CURRENCY_GBP = 'GBP';
    public const CURRENCY_EUR = 'EUR';
    public const CURRENCY_AUD = 'AUD';
    public const CURRENCY_NZD = 'NZD';
    public const CURRENCY_CAD = 'CAD';

    public const SUPPORTED_CURRENCIES = [
        self::CURRENCY_USD,
        self::CURRENCY_GBP,
        self::CURRENCY_EUR,
        self::CURRENCY_AUD,
        self::CURRENCY_NZD,
        self::CURRENCY_CAD,
    ];

    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'currency_rates';

    protected $guarded = [];

    public $timestamps = true;
}
