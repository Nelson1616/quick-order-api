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
}
