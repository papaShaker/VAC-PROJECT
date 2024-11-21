<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function insertData(Request $request)
    {
        $response = Test::create([
            'text' => $request
        ]);

        return response()->json($response, 201);
    }
}
