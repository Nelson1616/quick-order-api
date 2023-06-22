<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status_id',
        'image_id',
        'updated_at',
        'created_at',
    ];

    protected $hidden = [
        'password',
    ];

    public function sessionUsers(): HasMany
    {
        return $this->hasMany(SessionUser::class, 'user_id', 'id');
    }

    public function activeSessionUsers(): HasMany
    {
        return $this->hasMany(SessionUser::class, 'user_id', 'id')->where('status_id', '>', 0);
    }

    public static function getByLogin(string $email, string $password) : ?self {
        return self::where('email', $email)
        ->where('password', $password)
        ->first();
    }

    public function getRelations() {
        $this->activeSessionUsers;

        foreach ($this->activeSessionUsers as $sessionUser) {
            $sessionUser->amount_to_pay = $sessionUser->amountToPay();
            $sessionUser->session;
            $sessionUser->session->sessionUsers;

            foreach ($sessionUser->session->sessionUsers as $sessionUserFromSession) {
                $sessionUserFromSession->user;
            }

            $sessionUser->session->sessionOrders;

            foreach ($sessionUser->session->sessionOrders as $sessionOrder) {
                $sessionOrder->product;

                $sessionOrder->sessionOrderUsers;

                foreach ($sessionOrder->sessionOrderUsers as $orderUser) {
                    $orderUser->sessionUser;
                    $orderUser->sessionUser->user;
                }
            }

            $sessionUser->session->table;
            $sessionUser->session->table->restaurant;
            $sessionUser->session->table->restaurant->products;
        }
    }

    public static function createSimple(string $name, int $imageId) : self {
        return self::create([
            'name' => $name,
            'image_id' => $imageId,
        ]);
    }

    public static function getOrdersToPay(int $userId) {
        return DB::select("SELECT 
        u.id as user_id,
        u.name as user_name,
        su.id as session_user_id,
        so.id as session_order_id,
        sou.id as session_user_order_id,
        p.name as product_name,
        so.amount,
        so.amount_left,
        (
            SELECT
            COUNT(so2.id)
            FROM session_orders so2
            JOIN session_order_users sou2 ON sou2.session_order_id = so2.id 
            where so2.id = so.id
            AND sou2.status_id != 0
            AND so2.status_id != 4
            AND so2.status_id != 0
        ) as users_to_div,
        (so.amount_left / (
            SELECT
            COUNT(so2.id)
            FROM session_orders so2
            JOIN session_order_users sou2 ON sou2.session_order_id = so2.id 
            where so2.id = so.id
            AND sou2.status_id != 0
            AND so2.status_id != 4
            AND so2.status_id != 0
            )
        ) as price_to_pay
        FROM users u 
        JOIN session_users su ON su.user_id = u.id 
        JOIN session_order_users sou on sou.session_user_id = su.id 
        JOIN session_orders so on sou.session_order_id = so.id 
        JOIN products p on p.id = so.product_id 
        where 
        u.id = $userId
        AND sou.status_id != 0
        AND so.status_id != 0
        AND so.status_id != 4");
    }
}
