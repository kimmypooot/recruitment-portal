<?php

namespace App\Http\Controllers;

use App\Models\Competency;
use App\Models\VacancyCompetency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompetencyController extends Controller
{
    public function index(): JsonResponse
    {
        $competencies = Competency::orderBy('competency_group')
            ->orderBy('sort_order')
            ->orderBy('competency_name')
            ->withCount('vacancyCompetencies')
            ->get();

        return response()->json(['data' => $competencies]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'competency_name'  => 'required|string|max:255',
            'competency_group' => 'required|in:Core,Organizational,Leadership,Technical',
            'sort_order'       => 'nullable|integer|min:0|max:255',
            'description'      => 'nullable|string|max:1000',
        ]);

        $key = Str::snake(Str::lower($data['competency_name']));
        // Ensure uniqueness by appending a counter if needed
        $base = $key;
        $i    = 1;
        while (Competency::where('competency_key', $key)->exists()) {
            $key = "{$base}_{$i}";
            $i++;
        }

        $competency = Competency::create([
            'competency_key'   => $key,
            'competency_name'  => $data['competency_name'],
            'competency_group' => $data['competency_group'],
            'sort_order'       => $data['sort_order'] ?? 0,
            'description'      => $data['description'] ?? null,
        ]);

        return response()->json($competency->loadCount('vacancyCompetencies'), 201);
    }

    public function update(Request $request, Competency $competency): JsonResponse
    {
        $data = $request->validate([
            'competency_name'  => 'required|string|max:255',
            'competency_group' => 'required|in:Core,Organizational,Leadership,Technical',
            'sort_order'       => 'nullable|integer|min:0|max:255',
            'description'      => 'nullable|string|max:1000',
        ]);

        $competency->update([
            'competency_name'  => $data['competency_name'],
            'competency_group' => $data['competency_group'],
            'sort_order'       => $data['sort_order'] ?? $competency->sort_order,
            'description'      => $data['description'] ?? null,
        ]);

        return response()->json($competency->fresh()->loadCount('vacancyCompetencies'));
    }

    public function destroy(Competency $competency): JsonResponse
    {
        $usedCount = VacancyCompetency::where('competency_key', $competency->competency_key)->count();

        if ($usedCount > 0) {
            return response()->json([
                'message' => "Cannot delete: this competency is assigned to {$usedCount} vacancy/vacancies. Remove the assignments first.",
            ], 422);
        }

        $competency->delete();

        return response()->json(['success' => true]);
    }
}
