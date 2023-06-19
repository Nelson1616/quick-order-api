<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\General;
use App\Models\Product;
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

        if ($user->restaurant_id != null) {
            $user->restaurant;

            if ($user->restaurant == null) {
                throw new Exception("Funcionário não possui restaurante cadastrado", 500);
            } 

            $user->restaurant->tables;

            foreach ($user->restaurant->tables as $table) {
                $table->activeSessions;

                foreach ($table->activeSessions as $session) {
                    $session->sessionUsers;
    
                    foreach ($session->sessionUsers as $sessionUserFromSession) {
                        $sessionUserFromSession->user;
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
                }
            }
        }
        else {
            foreach ($user->activeSessionUsers as $sessionUser) {
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

    private static function verifyProduct(?Product $product) {
        if ($product == null) {
            throw new Exception("Produto não encontrado", 404);
        }

        $product->restaurant;

        if ($product->restaurant == null) {
            throw new Exception("Produto não possui restaurante cadastrado", 500);
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
                    "table_id" => $request->table_id
                ], 
                [
                    "user_name" => "required|string",
                    "user_image_id" => "required|integer",
                    "table_id" => "required|integer",
                ]
            );

            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
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

            $user = General::getUserById($user->id);

            self::getUserRelations($user);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function makeOrder(Request $request) {
        try {
            $validation = Validator::make(
                [
                    "user_id" => $request->user_id,
                    "session_id" => $request->session_id, 
                    "product_id" => $request->product_id, 
                    "quantity" => $request->quantity
                ], 
                [
                    "user_id" => "required|integer",
                    "session_id" => "required|integer",
                    "product_id" => "required|integer",
                    "quantity" => "required|integer|gt:0",
                ]
            );

            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $user = General::getUserById($request->user_id);

            self::verifyUser($user);

            self::getUserRelations($user);

            if ($user->activeSessionUsers->first() == null) {
                throw new Exception("Usuário não está em uma sessão", 400);
            }

            if($user->activeSessionUsers[0]->session_id != $request->session_id) {
                throw new Exception("Sessão do usuário é diferente da do pedido", 400);
            }

            $product = General::getProductById($request->product_id);

            self::verifyProduct($product);

            if ($product->restaurant_id != $user->activeSessionUsers[0]->session->table->restaurant_id) {
                throw new Exception("Produto é de restaurante diferente do solicitado", 400);
            }

            $sessionOrder = General::createSessionOrder($product->id, $request->session_id, $request->quantity, $product->price);

            if ($sessionOrder == null) {
                throw new Exception("Erro ao criar pedido", 500);
            }

            $sessionOrderUser = General::createSessionOrderUser($sessionOrder->id, $user->activeSessionUsers[0]->id);

            if ($sessionOrderUser == null) {
                throw new Exception("Erro ao vincular usuário ao pedido", 500);
            }

            $user = General::getUserById($request->user_id);

            self::verifyUser($user);

            self::getUserRelations($user);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function payOrders(Request $request) {
        try {
            $validation = Validator::make(
                [
                    "user_id" => $request->user_id,
                    "session_id" => $request->session_id, 
                ], 
                [
                    "user_id" => "required|integer",
                    "session_id" => "required|integer",
                ]
            );

            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $user = General::getUserById($request->user_id);

            self::verifyUser($user);

            self::getUserRelations($user);

            if ($user->activeSessionUsers->first() == null) {
                throw new Exception("Usuário não está em uma sessão", 400);
            }

            if($user->activeSessionUsers[0]->session_id != $request->session_id) {
                throw new Exception("Sessão do usuário é diferente da do pedido", 400);
            }

            $orders_to_pay = General::getOrdersToPay($request->user_id);

            foreach ($orders_to_pay as $order) {
                General::updateOrderUserToPaid($order->session_user_order_id);
                General::updateOrderAmountLeft($order->session_order_id, $order->amount_left - $order->price_to_pay);
                General::tryUpdateOrderToPaid($order->session_order_id);
            }

            General::tryUpdateSessionToPaid($user->activeSessionUsers[0]->session_id);

            $user->activeSessionUsers[0]->update(["status_id" => 0]);

            $user = General::getUserById($request->user_id);

            self::verifyUser($user);

            self::getUserRelations($user);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function helpWithOrder(Request $request) {
        try {
            $validation = Validator::make(["session_user_id" => $request->session_user_id, "session_order_id" => $request->session_order_id], [
                "session_user_id" => "required|integer",
                "session_order_id" => "required|integer",
            ]);
            
            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $sessionUser = General::getSessionUserById($request->session_user_id);

            if ($sessionUser == null) {
                throw new Exception("Usuário não encontrado na sessão", 400);
            }

            $sessionUser->session;

            $sessionOrder = General::getSessionOrderById($request->session_order_id);

            if ($sessionOrder == null) {
                throw new Exception("Pedido não encontrado na sessão", 400);
            }

            $sessionOrder->session;

            if ($sessionUser->session->id != $sessionOrder->session->id) {
                throw new Exception("Pedido e Usuária não são da mesma sessão", 400);
            }

            General::createSessionOrderUser($sessionOrder->id, $sessionUser->id);


            $user = General::getUserById($sessionUser->user->id);

            self::verifyUser($user);

            self::getUserRelations($user);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function setOrderAsDelivered(Request $request) {
        try {
            $validation = Validator::make(["user_id" => $request->user_id, "session_order_id" => $request->session_order_id], [
                "user_id" => "required|integer",
                "session_order_id" => "required|integer",
            ]);
            
            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $sessionOrder = General::getSessionOrderById($request->session_order_id);

            if ($sessionOrder == null) {
                throw new Exception("Pedido não encontrado na sessão", 400);
            }

            $sessionOrder->update(["status_id" => 2]);


            $user = General::getUserById($request->user_id);

            self::verifyUser($user);

            self::getUserRelations($user);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }
}
