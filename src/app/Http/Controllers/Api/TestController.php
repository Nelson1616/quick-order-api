<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test() {
        $notes = DB::select('SELECT * FROM restaurants;');
 
        return response()->json([
            "restaurants" => $notes
        ]);
    }

    public function env() {
        return response()->json([
            "env var" => getenv("APP_NAME"),
            "env from clound" => getenv("APP_NAME2"),
        ]);
    }

    public function db() {
        return response()->json([
            "db name" => DB::getDatabaseName(),
        ]);
    }
}
