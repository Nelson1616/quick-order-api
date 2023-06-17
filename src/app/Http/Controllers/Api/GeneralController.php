<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\General;
use App\Models\Table;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

class GeneralController extends Controller
{
    private static function verifyUser(?User $user) {
        if ($user == null) {
            throw new Exception("Usuário não encontrado", 404);
        }
    }

    private static function getUserRelations(User $user) {
        $user->activeSessionUsers;

        foreach ($user->activeSessionUsers as $sessionUser) {
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
                    $orderUser->user;
                }
            }

            $sessionUser->session->table;
            $sessionUser->session->table->restaurant;
            $sessionUser->session->table->restaurant->products;
        }
    }

    private static function verifyTable(?Table $table) {
        if ($table == null) {
            throw new Exception("Mesa não encontrada", 404);
        }

        $table->restaurant;

        if ($table->restaurant == null) {
            throw new Exception("Mesa não possui restaurante cadastrado", 500);
        }
    }

    public function login(Request $request) {
        try {
            $validation = Validator::make(["email" => $request->email, "password" => $request->password], [
                "email" => "required|email",
                "password" => "required|string",
            ]);
            
            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $user = General::getUserByLogin($request->email, $request->password);

            self::verifyUser($user);

            self::getUserRelations($user);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function getUser(int $id) {
        try {
            $validation = Validator::make(["id" => $id], [
                "id" => "required|integer"
            ]);

            if ($validation->fails()) {
                throw new Exception("ID inválido", 400);
            }

            $user = General::getUserById($id);

            self::verifyUser($user);

            self::getUserRelations($user);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function getTableByCode(string $code) {
        try {
            $validation = Validator::make(["code" => $code], [
                "code" => "required|string"
            ]);

            if ($validation->fails()) {
                throw new Exception("Código inválidos", 400);
            }

            $table = General::getTableByCode($code);

            self::verifyTable($table);

            return self::successdResponse($table);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function insertNewUserOnTable(Request $request) {
        try {
            $validation = Validator::make(
                [
                    "user_name" => $request->user_name,
                    "user_image_id" => $request->user_image_id, 
                    "table_id" => $request->table_id], 
                [
                    "user_name" => "required|string",
                    "user_image_id" => "required|integer",
                    "table_id" => "required|integer",
                ]
            );

            if ($validation->fails()) {
                throw new Exception("Código inválidos", 400);
            }

            $table = General::getTableById($request->table_id);

            self::verifyTable($table);

            if ($table->activeSessions()->first() == null) {
                General::createSession($table->id);
            }

            if ($table->activeSessions()->first() == null) {
                throw new Exception("Erro ao criar sessão", 500);
            }
            
            $session = $table->activeSessions[0];

            $verifyExistName = General::verifySessionUserSameName($session->id, $request->user_name);

            if ($verifyExistName != null || !empty($verifyExistName)) {
                throw new Exception("Nome $request->user_name já está sendo usado nesta mesa", 400);
            }

            $user = General::createSimpleUser($request->user_name, $request->user_image_id);

            self::verifyUser($user);

            General::createSessionUser($user->id, $session->id);

            self::getUserRelations($user);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }
}
