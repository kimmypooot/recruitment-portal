<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Vacancy;
use App\Notifications\ApplicationStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $query = Application::with([
            'vacancy:id,position_title,place_of_assignment,salary_grade',
            'applicant:id,user_id,first_name,last_name,middle_name,mobile_number,gender,civil_status,birthday',
            'applicant.user:id,name,email',
        ]);

        if ($request->filled('vacancy_id')) {
            $query->where('vacancy_id', $request->vacancy_id);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('applicant.user', fn ($u) => $u->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->paginate(20);

        return response()->json($applications);
    }

    public function vacancySummary(): JsonResponse
    {
        $vacancies = Vacancy::withCount('applications')
            ->with(['applications:id,vacancy_id,status'])
            ->orderByRaw("FIELD(status, 'published', 'draft', 'closed', 'filled', 'archived')")
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Vacancy $v) => [
                'id'                  => $v->id,
                'position_title'      => $v->position_title,
                'salary_grade'        => $v->salary_grade,
                'monthly_salary'      => $v->monthly_salary,
                'place_of_assignment' => $v->place_of_assignment,
                'item_number'         => $v->item_number,
                'status'              => $v->status,
                'published_at'        => $v->published_at,
                'deadline_at'         => $v->deadline_at,
                'applications_count'  => $v->applications_count,
                'status_breakdown'    => $v->applications->groupBy('status')->map->count(),
            ]);

        return response()->json($vacancies);
    }

    public function applicantProfile(Application $application): JsonResponse
    {
        $applicant = $application->applicant->load([
            'workExperiences',
            'educationalAttainments',
            'trainings',
        ]);

        return response()->json($applicant);
    }

    public function serveApplicantDocument(Request $request, Application $application, string $type): StreamedResponse
    {
        $this->authorize('view', $application);

        $map = [
            'pds'        => 'pds_path',
            'app_letter' => 'app_letter_path',
            'ipcr'       => 'ipcr_path',
            'coe'        => 'coe_path',
            'tor'        => 'tor_path',
        ];

        abort_if(!isset($map[$type]), 404);

        $profile = $application->applicant;
        $path    = $profile?->{$map[$type]};

        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        if ($request->boolean('download')) {
            return Storage::disk('public')->download($path);
        }

        return Storage::disk('public')->response($path);
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
