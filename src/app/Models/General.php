<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

class General extends Model
{
    use HasFactory;

    public static function getUserByLogin(string $email, string $password) {
        $validator = Validator::make(['email' => $email, 'password' => $password], [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            throw new Exception("Dados inválidos", 400);
        }

        $user = User::where('email', $email)
        ->where('password', $password)
        ->first();

        if ($user == null) {
            throw new Exception("Usuário não encontrado", 404);
        }
 
        return $user;
    }

    public static function getUserById(int $id) {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer',
        ]);
        
        if ($validator->fails()) {
            throw new Exception("Usuário inválido", 400);
        }

        $user = User::find($id);

        if ($user == null) {
            throw new Exception("Usuário não encontrado", 404);
        }
 
        return $user;
    }
}
