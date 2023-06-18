<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class General extends Model
{
    public static function getUserByLogin(string $email, string $password) : ?User {
        return User::where('email', $email)
        ->where('password', $password)
        ->first();
    }

    public static function getUserById(int $id) : ?User {
        return User::find($id);
    }

    public static function createSimpleUser(string $name, int $imageId) : ?User {
        return User::create([
            'name' => $name,
            'image_id' => $imageId,
        ]);
    }

    public static function getTableByCode(string $code) : ?Table {
        return Table::where('enter_code', $code)
        ->first();
    }

    public static function getTableById(int $id) : ?Table {
        return Table::find($id);
    }

    public static function createSession(int $table_id) : ?Session {
        return Session::create([
            'table_id' => $table_id,
        ]);
    }

    public static function createSessionUser(int $user_id, int $session_id) : ?SessionUser {
        return SessionUser::create([
            'user_id' => $user_id,
            'session_id' => $session_id,
        ]);
    }

    public static function verifySessionUserSameName(int $session_id, string $name) {
        return DB::select("SELECT 
        u.*
        FROM users u 
        JOIN session_users su ON su.user_id = u.id 
        WHERE
        u.name = '$name'
        AND su.session_id = $session_id");
    }

    public static function getProductById(int $id) : ?Product {
        return Product::find($id);
    }

    public static function createSessionOrder(int $product_id, int $session_id, int $quantity, int $price) : ?SessionOrder {
        return SessionOrder::create([
            'product_id' => $product_id,
            'session_id' => $session_id,
            'quantity' => $quantity,
            'amount' => $price * $quantity,
            'amount_left' => $price * $quantity,
        ]);
    }

    public static function createSessionOrderUser(int $session_order_id, int $session_user_id) : ?SessionOrderUser {
        return SessionOrderUser::firstOrCreate([
            'session_order_id' => $session_order_id,
            'session_user_id' => $session_user_id,
        ]);
    }

    public static function getSessionOrderUserById(int $session_order_user_id) : ?SessionOrderUser {
        return SessionOrderUser::find($session_order_user_id);
    }

    public static function getSessionUserById(int $session_user_id) : ?SessionUser {
        return SessionUser::find($session_user_id);
    }

    public static function getSessionOrderById(int $session_order_id) : ?SessionOrder {
        return SessionOrder::find($session_order_id);
    }

    public static function getOrdersToPay(int $user_id) {
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
            AND sou2.status_id > 0
        ) as users_to_div,
        (so.amount_left / (
            SELECT
            COUNT(so2.id)
            FROM session_orders so2
            JOIN session_order_users sou2 ON sou2.session_order_id = so2.id 
            where so2.id = so.id
            AND sou2.status_id > 0
            )
        ) as price_to_pay
        FROM users u 
        JOIN session_users su ON su.user_id = u.id 
        JOIN session_order_users sou on sou.session_user_id = su.id 
        JOIN session_orders so on sou.session_order_id = so.id 
        JOIN products p on p.id = so.product_id 
        where 
        u.id = $user_id
        AND sou.status_id > 0");
    }

    public static function updateOrderUserToPaid(int $session_order_user_id) {
        SessionOrderUser::find($session_order_user_id)->update(["status_id" => 0]);
    }

    public static function updateOrderAmountLeft(int $session_order_id, int $amount) {
        SessionOrder::find($session_order_id)->update(["amount_left" => $amount]);
    }

    public static function tryUpdateOrderToPaid(int $session_order_id) {
        $query = DB::select("SELECT
        sou.*
        FROM session_orders so 
        JOIN session_order_users sou ON sou.session_order_id = so.id
        WHERE 
        so.id = $session_order_id
        AND sou.status_id > 0");

        if (empty($query)) {
            SessionOrder::find($session_order_id)->update(["status_id" => 0]);
        }
    }

    public static function tryUpdateSessionToPaid(int $session_id) {
        $query = DB::select("SELECT 
        su.*
        FROM sessions s
        JOIN session_users su ON su.session_id = s.id
        WHERE 
        s.id = $session_id
        AND su.status_id > 0");

        if (empty($query)) {
            Session::find($session_id)->update(["status_id" => 0]);
        }
    }
}
