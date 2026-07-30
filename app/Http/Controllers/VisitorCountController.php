<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\VisitorCount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorCountController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'total' => VisitorCount::total(),
        ]);
    }

    public function increment(Request $request): JsonResponse
    {
        $page = $request->input('page', 'home');

        VisitorCount::incrementFor($page);

        return response()->json([
            'total' => VisitorCount::total(),
        ]);
    }
}
