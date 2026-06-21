<?php

namespace App\Http\Controllers;

use App\Models\ExamSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ExamSchedule::all());
    }

    public function store(Request $request): JsonResponse
    {
        $exam = ExamSchedule::create($request->validated());
        return response()->json($exam, 201);
    }

    public function show(ExamSchedule $examination): JsonResponse
    {
        return response()->json($examination);
    }

    public function update(Request $request, ExamSchedule $examination): JsonResponse
    {
        $examination->update($request->validated());
        return response()->json($examination);
    }

    public function destroy(ExamSchedule $examination): JsonResponse
    {
        $examination->delete();
        return response()->json(['message' => 'Deleted.']);
    }
}
