<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generate(Request $request, string $type): JsonResponse
    {
        return response()->json(['message' => "Report type '{$type}' not yet implemented."]);
    }
}
