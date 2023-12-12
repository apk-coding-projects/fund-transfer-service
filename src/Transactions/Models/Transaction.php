<?php

namespace src\Transactions\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use src\Accounts\Models\Account;

/**
 * @property int $id
 * @property int $sender_account_id
 * @property int $receiver_account_id
 * @property string $currency
 * @property float $amount
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read Account $senderAccount
 * @property-read Account $receiverAccount
 */
class Transaction extends Model
{
    use HasFactory;

    public $timestamps = true;

    public const STATUS_SUCCESS = 'success';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_FAILURE = 'failure';

    public const STATUSES = [self::STATUS_SUCCESS, self::STATUS_PROCESSING, self::STATUS_FAILURE];

    /**
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function senderAccount()
    {
        return $this->belongsTo(Account::class, 'sender_account_id', 'id');
    }

    public function receiverAccount()
    {
        return $this->belongsTo(Account::class, 'receiver_account_id', 'id');
    }

    protected static function newFactory()
    {
        return new TransactionFactory();
    }
}
