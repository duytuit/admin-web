<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function NotFound(Request $request)
    {
        return response()->json([
            'message' => 'Page Not Found',
        ], 404);
    }
}
