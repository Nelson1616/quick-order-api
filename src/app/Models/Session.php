<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'status_id',
        'table_id',
        'updated_at',
        'created_at',
    ];

    protected $hidden = [];

    public function table(): BelongsTo
    {
        return $this->BelongsTo(Table::class, 'table_id', 'id');
    }

    public function sessionUsers(): HasMany
    {
        return $this->hasMany(SessionUser::class, 'session_id', 'id');
    }

    public function sessionOrders(): HasMany
    {
        return $this->hasMany(SessionOrder::class, 'session_id', 'id');
    }
}
