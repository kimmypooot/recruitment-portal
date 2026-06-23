<?php

namespace App\Http\Controllers;

use App\Models\Competency;
use App\Models\Vacancy;
use App\Models\VacancyCompetency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VacancyCompetencyController extends Controller
{
    public function index(): JsonResponse
    {
        $competencies = Competency::orderBy('competency_group')
            ->orderBy('sort_order')
            ->orderBy('competency_name')
            ->get();

        return response()->json(['data' => $competencies]);
    }

    public function byVacancy(Vacancy $vacancy): JsonResponse
    {
        $assigned = VacancyCompetency::with('competency')
            ->where('vacancy_id', $vacancy->id)
            ->get()
            ->map(fn ($vc) => [
                'competency_key'   => $vc->competency_key,
                'competency_level' => $vc->competency_level,
                'competency_name'  => $vc->competency?->competency_name,
                'competency_group' => $vc->competency?->competency_group,
                'description'      => $vc->competency?->description,
            ]);

        return response()->json(['data' => $assigned]);
    }

    public function sync(Request $request, Vacancy $vacancy): JsonResponse
    {
        $request->validate([
            'competencies'                  => 'required|array',
            'competencies.*.competency_key' => 'required|string|exists:competencies,competency_key',
            'competencies.*.level'          => 'required|integer|between:1,4',
        ]);

        $incoming = collect($request->competencies)->keyBy('competency_key');

        $existing = VacancyCompetency::where('vacancy_id', $vacancy->id)
            ->pluck('competency_key')
            ->flip();

        foreach ($incoming as $key => $data) {
            VacancyCompetency::updateOrCreate(
                ['vacancy_id' => $vacancy->id, 'competency_key' => $key],
                ['competency_level' => $data['level']]
            );
        }

        VacancyCompetency::where('vacancy_id', $vacancy->id)
            ->whereNotIn('competency_key', $incoming->keys()->all())
            ->delete();

        return response()->json(['success' => true]);
    }

    public function remove(Vacancy $vacancy, string $competencyKey): JsonResponse
    {
        VacancyCompetency::where('vacancy_id', $vacancy->id)
            ->where('competency_key', $competencyKey)
            ->delete();

        return response()->json(['success' => true]);
    }
}
