<?php

namespace src\Transactions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use src\Accounts\Models\Account;
use src\Accounts\Models\AccountsHistory;

/**
 * @property int $id
 * @property int $sender_account_id
 * @property int $receiver_account_id
 * @property string $currency
 * @property float $amount
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class Transaction extends Model
{
    use HasFactory;

    public $timestamps = true;

    public function senderAccount()
    {
        return $this->belongsTo(Account::class, 'sender_account_id', 'id');
    }

    public function recieverAccount()
    {
        return $this->belongsTo(Account::class, 'receiver_account_id', 'id');
    }
}
