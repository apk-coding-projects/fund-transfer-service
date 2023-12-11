<?php

namespace src\Accounts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $account_id
 * @property string $currency
 * @property float $amount
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read Account $account
 */
class AccountsHistory extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'accounts_history';

    public $timestamps = true;

    protected $guarded = [];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
