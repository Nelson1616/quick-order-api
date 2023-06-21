<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

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

    public static function createNew(int $productId, int $sessionId, int $quantity, int $price) : self {
        return self::create([
            'product_id' => $productId,
            'session_id' => $sessionId,
            'quantity' => $quantity,
            'amount' => $price * $quantity,
            'amount_left' => $price * $quantity,
        ]);
    }

    public static function tryUpdateToPaid(int $session_order_id) {
        $query = DB::select("SELECT
        sou.*
        FROM session_orders so 
        JOIN session_order_users sou ON sou.session_order_id = so.id
        WHERE 
        so.id = $session_order_id
        AND sou.status_id > 0");

        if (empty($query)) {
            self::find($session_order_id)->update(["status_id" => 0]);
        }
    }
}
