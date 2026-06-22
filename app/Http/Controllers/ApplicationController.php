<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Vacancy;
use App\Notifications\ApplicationStatusUpdated;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->applicantProfile;

        if (!$profile) {
            return response()->json([]);
        }

        $applications = Application::where('applicant_id', $profile->id)
            ->with(['vacancy', 'examSchedule', 'interviewSchedule'])
            ->latest()
            ->get();

        return response()->json($applications);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'applicant') {
            return response()->json(['message' => 'Only applicants may submit applications.'], 403);
        }

        $data = $request->validate([
            'vacancy_id' => 'required|exists:vacancies,id',
        ]);

        $profile = $request->user()->applicantProfile;

        if (!$profile) {
            return response()->json(['message' => 'Please complete your profile before applying.'], 422);
        }

        if (!$profile->isComplete()) {
            return response()->json(['message' => 'Your profile is incomplete. Please fill in all required fields before applying.'], 422);
        }

        $vacancy = Vacancy::findOrFail($data['vacancy_id']);

        if ($vacancy->status !== 'published') {
            return response()->json(['message' => 'This vacancy is no longer accepting applications.'], 422);
        }

        $existing = Application::where('applicant_id', $profile->id)
            ->where('vacancy_id', $data['vacancy_id'])
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You have already applied for this vacancy.'], 422);
        }

        $application = Application::create([
            'vacancy_id'   => $data['vacancy_id'],
            'applicant_id' => $profile->id,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json($application->load('vacancy'), 201);
    }

    public function show(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        return response()->json($application->load(['vacancy', 'documents']));
    }

    public function hrIndex(Request $request): JsonResponse
    {
        $query = Application::with(['vacancy:id,position_title,place_of_assignment,salary_grade', 'applicant.user:id,name,email']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('vacancy', fn ($v) => $v->where('position_title', 'like', "%{$q}%"))
                    ->orWhereHas('applicant.user', fn ($u) => $u->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->paginate(20);

        return response()->json($applications);
    }

    public function updateStatus(Request $request, Application $application): JsonResponse
    {
        $request->validate([
            'status'  => 'required|in:under_review,screened,qualified,disqualified,exam_scheduled,interviewed,shortlisted,for_interview,recommended,appointed,completed,withdrawn',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $application->status;

        $application->update([
            'status'      => $request->status,
            'remarks'     => $request->remarks,
            'reviewed_at' => now(),
        ]);

        AuditLog::record("application_status_changed:{$oldStatus}→{$request->status}", $application);

        // Notify the applicant via their user account
        $applicantUser = $application->applicant?->user;
        if ($applicantUser) {
            $applicantUser->notify(new ApplicationStatusUpdated($application, $oldStatus, $request->status));
        }

        return response()->json([
            'message' => 'Application status updated successfully.',
            'data'    => $application->fresh(),
        ]);
    }
}
