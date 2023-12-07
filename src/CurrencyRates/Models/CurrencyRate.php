<?php

namespace src\CurrencyRates\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'currency_rates';
}
