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
 *
 * We use this model to save account historical data - all changes that happen to it, similar to DWH full history
 * For example, smth went wrong, customer complains that 2 days ago their balance was different. We have a log
 * => we can easily check it.
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
