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
}
