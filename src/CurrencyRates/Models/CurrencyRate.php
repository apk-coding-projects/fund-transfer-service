<?php

namespace src\CurrencyRates\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use src\CurrencyRates\Structures\Currency;

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

    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'currency_rates';

    protected $guarded = [];

    public $timestamps = true;
}
