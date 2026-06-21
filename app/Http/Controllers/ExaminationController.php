<?php

namespace App\Http\Controllers;

use App\Models\ExamSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ExamSchedule::with('application')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'scheduled_at'   => 'required|date|after:now',
            'venue'          => 'required|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $exam = ExamSchedule::create($data);

        return response()->json($exam, 201);
    }

    public function show(ExamSchedule $examination): JsonResponse
    {
        return response()->json($examination->load('application'));
    }

    public function update(Request $request, ExamSchedule $examination): JsonResponse
    {
        $data = $request->validate([
            'scheduled_at' => 'sometimes|required|date',
            'venue'        => 'sometimes|required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $examination->update($data);

        return response()->json($examination);
    }

    public function destroy(ExamSchedule $examination): JsonResponse
    {
        $examination->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
