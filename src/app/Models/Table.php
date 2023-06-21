<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    protected $table = 'tables';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'status_id',
        'restaurant_id',
        'enter_code',
        'updated_at',
        'created_at',
    ];

    protected $hidden = [];

    public function restaurant(): BelongsTo
    {
        return $this->BelongsTo(Restaurant::class, 'restaurant_id', 'id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'table_id', 'id');
    }

    public function activeSessions(): HasMany
    {
        return $this->hasMany(Session::class, 'table_id', 'id')->where('status_id', '>', 0);
    }

    public static function getByCode(string $code) : ?self {
        return self::where('enter_code', $code)
        ->first();
    }
}
