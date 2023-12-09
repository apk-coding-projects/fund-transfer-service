<?php

namespace src\Clients\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use src\Accounts\Models\Account;

/**
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string $full_name
 * @property string $email
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read Account[] $accounts
 */
class Client extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected static function newFactory()
    {
        return new ClientFactory();
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
