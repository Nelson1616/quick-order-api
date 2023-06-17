<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\General;
use Exception;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public static function failedResponse(Exception $e) {
        return response()->json([
            'success' => false,
            'data' => [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ],
            'msg' => $e->getMessage(),
        ]);
    }

    public static function successdResponse($data) {
        return response()->json([
            'success' => true,
            'data' => $data,
            'msg' => "",
        ]);
    } 

    public function login(Request $request) {
        try {
            $user = General::getUserByLogin($request->email, $request->password);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }

    public function getUser($id) {
        try {
            $user = General::getUserById($id);

            return self::successdResponse($user);
        }
        catch (Exception $e) {
            return self::failedResponse($e);
        } 
    }
}
