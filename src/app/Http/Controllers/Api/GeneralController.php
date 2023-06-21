<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Official;
use App\Models\Product;
use App\Models\Session;
use App\Models\SessionOrder;
use App\Models\SessionOrderUser;
use App\Models\SessionUser;
use App\Models\SessionWaiterCall;
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

    private static function verifyOfficial(?Official $official) {
        if ($official == null) {
            throw new Exception("Funcionário não encontrado", 404);
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

            $official = Official::getByLogin($request->email, $request->password);

            if ($official != null) {
                $official->getRelations();
                return self::successdResponse($official);
            }

            $user = User::getByLogin($request->email, $request->password);

            self::verifyUser($user);

            $user->getRelations();

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

            $user = User::find($id);

            self::verifyUser($user);

            $user->getRelations();

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function getOfficial(int $id) {
        try {
            $validation = Validator::make(["id" => $id], [
                "id" => "required|integer"
            ]);

            if ($validation->fails()) {
                throw new Exception("ID inválido", 400);
            }

            $official = Official::find($id);

            self::verifyOfficial($official);

            $official->getRelations();

            return self::successdResponse($official);
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

            $table = Table::getByCode($code);

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

            $table = Table::find($request->table_id);

            self::verifyTable($table);

            if ($table->activeSessions()->first() == null) {
                Session::createNew($table->id);
            }

            if ($table->activeSessions()->first() == null) {
                throw new Exception("Erro ao criar sessão", 500);
            }
            
            $session = $table->activeSessions[0];

            $verifyExistName = Session::verifyUserSameName($session->id, $request->user_name);

            if ($verifyExistName != null || !empty($verifyExistName)) {
                throw new Exception("Nome $request->user_name já está sendo usado nesta mesa", 400);
            }

            $user = User::createSimple($request->user_name, $request->user_image_id);

            self::verifyUser($user);

            SessionUser::createNew($user->id, $session->id);

            $user = User::find($user->id);

            $user->getRelations();

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

            $user = User::find($request->user_id);

            self::verifyUser($user);

            $user->getRelations();

            if ($user->activeSessionUsers->first() == null) {
                throw new Exception("Usuário não está em uma sessão", 400);
            }

            if($user->activeSessionUsers[0]->session_id != $request->session_id) {
                throw new Exception("Sessão do usuário é diferente da do pedido", 400);
            }

            $product = Product::find($request->product_id);

            self::verifyProduct($product);

            if ($product->restaurant_id != $user->activeSessionUsers[0]->session->table->restaurant_id) {
                throw new Exception("Produto é de restaurante diferente do solicitado", 400);
            }

            $sessionOrder = SessionOrder::createNew($product->id, $request->session_id, $request->quantity, $product->price);

            if ($sessionOrder == null) {
                throw new Exception("Erro ao criar pedido", 500);
            }

            $sessionOrderUser = SessionOrderUser::createNew($sessionOrder->id, $user->activeSessionUsers[0]->id);

            if ($sessionOrderUser == null) {
                throw new Exception("Erro ao vincular usuário ao pedido", 500);
            }

            $user = User::find($request->user_id);

            self::verifyUser($user);

            $user->getRelations();

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

            $user = User::find($request->user_id);

            self::verifyUser($user);

            $user->getRelations();

            if ($user->activeSessionUsers->first() == null) {
                throw new Exception("Usuário não está em uma sessão", 400);
            }

            if($user->activeSessionUsers[0]->session_id != $request->session_id) {
                throw new Exception("Sessão do usuário é diferente da do pedido", 400);
            }

            $orders_to_pay = User::getOrdersToPay($request->user_id);

            foreach ($orders_to_pay as $order) {
                SessionOrderUser::find($order->session_user_order_id)->update(["status_id" => 0]);
                SessionOrder::find($order->session_order_id)->update(["amount_left" => $order->amount_left - $order->price_to_pay]);
                SessionOrder::tryUpdateToPaid($order->session_order_id);
            }

            $user->activeSessionUsers[0]->update(["status_id" => 0]);

            Session::tryUpdateToFinished($user->activeSessionUsers[0]->session_id);

            $user = User::find($request->user_id);

            self::verifyUser($user);

            $user->getRelations();

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function helpWithOrder(Request $request) {
        try {
            $validation = Validator::make(["user_id" => $request->user_id, "session_order_id" => $request->session_order_id], [
                "user_id" => "required|integer",
                "session_order_id" => "required|integer",
            ]);
            
            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $user = User::find($request->user_id);

            self::verifyUser($user);

            $user->getRelations();

            if ($user->activeSessionUsers->first() == null) {
                throw new Exception("Usuário não está em uma sessão", 400);
            }

            $sessionUser = $user->activeSessionUsers->first();

            $sessionUser->session;

            $sessionOrder = SessionOrder::find($request->session_order_id);

            if ($sessionOrder == null) {
                throw new Exception("Pedido não encontrado na sessão", 400);
            }

            $sessionOrder->session;

            if ($sessionUser->session->id != $sessionOrder->session->id) {
                throw new Exception("Pedido e Usuária não são da mesma sessão", 400);
            }

            SessionOrderUser::createNew($sessionOrder->id, $sessionUser->id);


            $user = User::find($sessionUser->user->id);

            self::verifyUser($user);

            $user->getRelations();

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function notHelpWithOrder(Request $request) {
        try {
            $validation = Validator::make(["user_id" => $request->user_id, "session_order_id" => $request->session_order_id], [
                "user_id" => "required|integer",
                "session_order_id" => "required|integer",
            ]);
            
            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $user = User::find($request->user_id);

            self::verifyUser($user);

            $user->getRelations();

            if ($user->activeSessionUsers->first() == null) {
                throw new Exception("Usuário não está em uma sessão", 400);
            }

            $sessionUser = $user->activeSessionUsers->first();

            $sessionUser->session;

            $sessionOrder = SessionOrder::find($request->session_order_id);

            if ($sessionOrder == null) {
                throw new Exception("Pedido não encontrado na sessão", 400);
            }

            $sessionOrder->session;

            if ($sessionUser->session->id != $sessionOrder->session->id) {
                throw new Exception("Pedido e Usuária não são da mesma sessão", 400);
            }

            $sessionOrder->sessionOrderUsers;

            if (count($sessionOrder->sessionOrderUsers) < 2) {
                throw new Exception("Não há usuários suficentes ajudando com esse pedido", 500);
            }

            SessionOrderUser::where(
                'session_order_id', $sessionOrder->id)
                ->where('session_user_id', $sessionUser->id)
                ->delete();


            $user = User::find($sessionUser->user->id);

            self::verifyUser($user);

            $user->getRelations();

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function updateOrderStatus(Request $request) {
        try {
            $validation = Validator::make(["official_id" => $request->official_id, "session_order_id" => $request->session_order_id], [
                "official_id" => "required|integer",
                "session_order_id" => "required|integer",
            ]);
            
            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $sessionOrder = SessionOrder::find($request->session_order_id);

            if ($sessionOrder == null) {
                throw new Exception("Pedido não encontrado na sessão", 400);
            }

            if ($sessionOrder->status_id == 0) {
                throw new Exception("O pedido já foi pago", 400);
            }

            if ($sessionOrder->status_id > 2) {
                throw new Exception("Status inválido", 400);
            }

            $official = Official::find($request->official_id);

            self::verifyOfficial($official);

            $sessionOrder->update(["status_id" => $sessionOrder->status_id + 1]);


            $official = Official::find($request->official_id);

            self::verifyOfficial($official);

            $official->getRelations();

            return self::successdResponse($official);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function cancelOrder(Request $request) {
        try {
            $validation = Validator::make(["official_id" => $request->official_id, "session_order_id" => $request->session_order_id], [
                "official_id" => "required|integer",
                "session_order_id" => "required|integer",
            ]);
            
            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $sessionOrder = SessionOrder::find($request->session_order_id);

            if ($sessionOrder == null) {
                throw new Exception("Pedido não encontrado na sessão", 400);
            }

            if ($sessionOrder->status_id == 0) {
                throw new Exception("O pedido já foi pago", 400);
            }

            $official = Official::find($request->official_id);

            self::verifyOfficial($official);

            $sessionOrder->update(["status_id" => 4]);


            $official = Official::find($request->official_id);

            self::verifyOfficial($official);

            $official->getRelations();

            return self::successdResponse($official);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function makeWaiterCall(Request $request) {
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

            $user = User::find($request->user_id);

            self::verifyUser($user);

            $user->getRelations();

            if ($user->activeSessionUsers->first() == null) {
                throw new Exception("Usuário não está em uma sessão", 400);
            }

            if($user->activeSessionUsers[0]->session_id != $request->session_id) {
                throw new Exception("Sessão do usuário é diferente da do pedido", 400);
            }

            $waiterCall = SessionWaiterCall::createNew($request->session_id, $user->activeSessionUsers[0]->id);

            if ($waiterCall == null) {
                throw new Exception("Erro ao criar pedido", 500);
            }

            $user = User::find($request->user_id);

            self::verifyUser($user);

            $user->getRelations();

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function updateWaiterCall(Request $request) {
        try {
            $validation = Validator::make(
                [
                    "user_id" => $request->user_id,
                    "session_waiter_call_id" => $request->session_waiter_call_id, 
                ], 
                [
                    "user_id" => "required|integer",
                    "session_waiter_call_id" => "required|integer",
                ]
            );

            if ($validation->fails()) {
                throw new Exception("Dados inválidos", 400);
            }

            $waiterCall = SessionWaiterCall::find($request->session_waiter_call_id);

            if ($waiterCall == null) {
                throw new Exception("Chamado não encontrado", 404);
            }

            $user = User::find($request->user_id);

            self::verifyUser($user);

            if ($user->restaurant_id == null) {
                throw new Exception("Usuário inválido para esta ação", 400);
            }

            $waiterCall->update(["status_id" => 2]);

            $user = User::find($request->user_id);

            self::verifyUser($user);

            $user->getRelations();

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }
}
