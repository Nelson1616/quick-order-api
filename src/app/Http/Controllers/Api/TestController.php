<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test() {
        $notes = DB::select('SELECT * FROM test_notes tn;');
 
        return response()->json([
            "notes" => $notes
        ]);
    }
}
