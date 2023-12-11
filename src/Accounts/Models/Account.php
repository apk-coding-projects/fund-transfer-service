<?php

namespace src\Accounts\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property-read AccountsHistory[] $accountHistory
 */
class Account extends Model
{
    use HasFactory;

    public $timestamps = true;

    /**
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected static function newFactory(): AccountFactory
    {
        return new AccountFactory();
    }

    protected static function booted(): void
    {
        // TODO check
        static::saved(function(Account $account) {
            AccountsHistory::create([
                'account_id' => $account->id,
                'currency' => $account->currency,
                'amount' => $account->amount,
            ]);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function accountHistory(): HasMany
    {
        return $this->hasMany(AccountsHistory::class, 'account_id', 'id');
    }
}
