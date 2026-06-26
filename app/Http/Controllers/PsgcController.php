<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PsgcController extends Controller
{
    public function barangays(string $cityCode): JsonResponse
    {
        $path = base_path('resources/js/data/barangays.json');

        if (!file_exists($path)) {
            return response()->json([], 200);
        }

        $all = json_decode(file_get_contents($path), true);

        $barangays = array_values(array_filter($all, fn($b) =>
            ($b['city_code'] ?? '') === $cityCode
        ));

        usort($barangays, fn($a, $b) => strcmp($a['brgy_name'] ?? '', $b['brgy_name'] ?? ''));

        return response()->json($barangays);
    }
}
