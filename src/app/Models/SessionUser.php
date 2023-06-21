<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionUser extends Model
{
    protected $table = 'session_users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'status_id',
        'session_id',
        'user_id',
        'amount_to_pay',
        'updated_at',
        'created_at',
    ];

    protected $hidden = [];

    public function session(): BelongsTo
    {
        return $this->BelongsTo(Session::class, 'session_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'user_id', 'id');
    }

    public function amountToPay() : int {
        $amount = 0;

        $query = User::getOrdersToPay($this->user_id);

        foreach($query as $order) {
            $amount += $order->price_to_pay;
        }

        return $amount;
    }

    public static function createNew(int $userId, int $sessionId) : self {
        return self::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
        ]);
    }
}
