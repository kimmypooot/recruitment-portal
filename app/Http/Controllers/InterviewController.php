<?php

namespace App\Http\Controllers;

use App\Models\InterviewSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(InterviewSchedule::with('application')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'scheduled_at'   => 'required|date|after:now',
            'venue'          => 'required|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $interview = InterviewSchedule::create($data);

        return response()->json($interview, 201);
    }

    public function show(InterviewSchedule $interview): JsonResponse
    {
        return response()->json($interview->load('application'));
    }

    public function update(Request $request, InterviewSchedule $interview): JsonResponse
    {
        $data = $request->validate([
            'scheduled_at' => 'sometimes|required|date',
            'venue'        => 'sometimes|required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $interview->update($data);

        return response()->json($interview);
    }

    public function destroy(InterviewSchedule $interview): JsonResponse
    {
        $interview->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
