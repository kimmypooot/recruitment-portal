<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Vacancy;
use App\Notifications\ApplicationStatusUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $applications = Application::where('applicant_id', $request->user()->id)
            ->with('vacancy')
            ->latest()
            ->get();

        return response()->json($applications);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vacancy_id' => 'required|exists:vacancies,id',
        ]);

        $application = Application::create([
            'vacancy_id'   => $data['vacancy_id'],
            'applicant_id' => $request->user()->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json($application, 201);
    }

    public function show(Application $application): JsonResponse
    {
        return response()->json($application->load(['vacancy', 'documents']));
    }

    public function hrIndex(Request $request): JsonResponse
    {
        $applications = Application::with(['vacancy', 'applicant'])
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }

    public function updateStatus(Request $request, Application $application): JsonResponse
    {
        $request->validate([
            'status'  => 'required|in:under_review,screened,qualified,disqualified,shortlisted,for_interview,recommended,appointed,completed',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $application->status;

        $application->update([
            'status'      => $request->status,
            'remarks'     => $request->remarks,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Application status updated successfully.',
            'data'    => $application->fresh(),
        ]);
    }
}
