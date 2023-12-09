<?php

namespace src\Accounts\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use src\Clients\Models\Client;

/**
 * @property int $id
 * @property int $client_id
 * @property string $currency
 * @property float $amount
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read Client $client
 */
class Account extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected static function newFactory()
    {
        return new AccountFactory();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
