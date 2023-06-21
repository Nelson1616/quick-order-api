<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Official extends Model
{
    protected $table = 'officials';
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
        'restaurant_id',
        'updated_at',
        'created_at',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->BelongsTo(Restaurant::class, 'restaurant_id', 'id');
    }

    public static function getByLogin(string $email, string $password) : ?self {
        return self::where('email', $email)
        ->where('password', $password)
        ->first();
    }

    public function getRelations() {
        $this->restaurant;

        $this->restaurant->tables;

        foreach ($this->restaurant->tables as $table) {
            $table->activeSessions;

            foreach ($table->activeSessions as $session) {
                $session->sessionUsers;

                foreach ($session->sessionUsers as $sessionUser) {
                    $sessionUser->user;
                }

                $session->sessionOrders;

                foreach ($session->sessionOrders as $sessionOrder) {
                    $sessionOrder->product;

                    $sessionOrder->sessionOrderUsers;

                    foreach ($sessionOrder->sessionOrderUsers as $orderUser) {
                        $orderUser->sessionUser;
                        $orderUser->sessionUser->user;
                    }
                }

                $session->sessionWaiterCalls;

                foreach ($session->sessionWaiterCalls as $sessionWaiterCall) {
                    $sessionWaiterCall->sessionUser;
                    $sessionWaiterCall->sessionUser->user;
                }
            }
        }
    }
}
