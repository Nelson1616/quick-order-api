<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionOrderUser extends Model
{
    protected $table = 'session_order_users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'status_id',
        'session_order_id',
        'session_user_id',
        'updated_at',
        'created_at',
    ];

    public function sessionOrder(): BelongsTo
    {
        return $this->BelongsTo(SessionOrder::class, 'session_order_id', 'id');
    }

    public function sessionUser(): BelongsTo
    {
        return $this->BelongsTo(SessionUser::class, 'session_user_id', 'id');
    }
}
