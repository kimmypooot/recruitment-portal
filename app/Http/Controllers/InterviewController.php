<?php

namespace App\Http\Controllers;

use App\Models\InterviewSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(InterviewSchedule::all());
    }

    public function store(Request $request): JsonResponse
    {
        $interview = InterviewSchedule::create($request->validated());
        return response()->json($interview, 201);
    }

    public function show(InterviewSchedule $interview): JsonResponse
    {
        return response()->json($interview);
    }

    public function update(Request $request, InterviewSchedule $interview): JsonResponse
    {
        $interview->update($request->validated());
        return response()->json($interview);
    }

    public function destroy(InterviewSchedule $interview): JsonResponse
    {
        $interview->delete();
        return response()->json(['message' => 'Deleted.']);
    }
}
