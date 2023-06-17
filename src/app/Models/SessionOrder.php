<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SessionOrder extends Model
{
    protected $table = 'session_orders';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'status_id',
        'session_id',
        'product_id',
        'quantity',
        'amount',
        'amount_left',
        'updated_at',
        'created_at',
    ];

    public function session(): BelongsTo
    {
        return $this->BelongsTo(Session::class, 'session_id', 'id');
    }

    public function sessionOrderUsers(): HasMany
    {
        return $this->hasMany(SessionOrderUser::class, 'session_order_id', 'id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
