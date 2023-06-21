<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SessionWaiterCall extends Model
{
    protected $table = 'session_waiter_calls';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'status_id',
        'session_id',
        'session_user_id',
        'updated_at',
        'created_at',
    ];

    public function session(): BelongsTo
    {
        return $this->BelongsTo(Session::class, 'session_id', 'id');
    }

    public function sessionUser(): BelongsTo
    {
        return $this->BelongsTo(SessionUser::class, 'session_user_id', 'id');
    }

    public static function createNew(int $sessionId, int $sessionUserId) : self {
        return self::create([
            'session_id' => $sessionId,
            'session_user_id' => $sessionUserId,
        ]);
    }
}
