<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

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
}
