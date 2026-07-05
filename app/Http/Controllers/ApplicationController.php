<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Vacancy;
use App\Notifications\ApplicationStatusUpdated;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\ShortlistResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
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

        if (!$profile->hasRequiredDocuments()) {
            return response()->json(['message' => 'Please upload all required documents (PDS, Application Letter, Certificate of Eligibility, and Transcript of Records) before applying.'], 422);
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

        $request->user()->notify(new ApplicationSubmitted($application->load('vacancy')));

        return response()->json($application->load('vacancy'), 201);
    }

    public function show(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        return response()->json($application->load(['vacancy', 'documents']));
    }

    public function hrIndex(Request $request): JsonResponse
    {
        $query = $this->hrFilteredQuery($request)->with([
            'vacancy:id,position_title,place_of_assignment,salary_grade',
            'applicant:id,user_id,mobile_number,gender,civil_status,birthday',
            'applicant.user:id,first_name,last_name,middle_name,suffix,email',
        ]);

        $this->applySort($query, $request);

        $applications = $query->paginate(20)->withQueryString();

        return response()->json($this->paginatedResponse($applications));
    }

    /**
     * Stream all applications matching the current HR filters as CSV
     * (ignores pagination — exports the full filtered result set).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->hrFilteredQuery($request)->with([
            'vacancy:id,position_title,place_of_assignment,salary_grade',
            'applicant:id,user_id,mobile_number,gender,civil_status,birthday',
            'applicant.user:id,first_name,last_name,middle_name,suffix,email',
        ])->latest();

        $filename = 'applicants-'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Last Name', 'First Name', 'Middle Name', 'Email', 'Gender',
                'Civil Status', 'Birthday', 'Mobile Number', 'Position',
                'Status', 'Submitted At',
            ], ',', '"', '\\');

            $query->chunk(200, function ($chunk) use ($out) {
                foreach ($chunk as $app) {
                    $user = $app->applicant?->user;
                    fputcsv($out, [
                        $user?->last_name,
                        $user?->first_name,
                        $user?->middle_name,
                        $user?->email,
                        $app->applicant?->gender,
                        $app->applicant?->civil_status,
                        $app->applicant?->birthday?->format('Y-m-d'),
                        $app->applicant?->mobile_number,
                        $app->vacancy?->position_title,
                        $app->status,
                        $app->submitted_at?->format('Y-m-d H:i'),
                    ], ',', '"', '\\');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Base query for the HR/admin applications list, shared by hrIndex and export.
     */
    private function hrFilteredQuery(Request $request): Builder
    {
        $query = Application::query();

        if ($request->filled('vacancy_id')) {
            $query->where('vacancy_id', $request->vacancy_id);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('applicant.user', fn ($u) => $u->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    /**
     * Apply server-side sorting for sortable HR list columns.
     * Falls back to newest-first when no recognized sort_by is given.
     */
    private function applySort(Builder $query, Request $request): void
    {
        $sortBy = $request->input('sort_by');
        $sortDir = $request->input('sort_dir') === 'desc' ? 'desc' : 'asc';

        $directColumns = [
            'gender' => 'applicant_profiles.gender',
            'civil_status' => 'applicant_profiles.civil_status',
            'birthday' => 'applicant_profiles.birthday',
            'mobile' => 'applicant_profiles.mobile_number',
        ];

        if ($sortBy === 'status') {
            $query->orderBy('applications.status', $sortDir);

            return;
        }

        if ($sortBy === 'name' || $sortBy === 'email') {
            $query->join('applicant_profiles', 'applicant_profiles.id', '=', 'applications.applicant_id')
                ->join('users', 'users.id', '=', 'applicant_profiles.user_id')
                ->select('applications.*');

            if ($sortBy === 'name') {
                $query->orderBy('users.last_name', $sortDir)->orderBy('users.first_name', $sortDir);
            } else {
                $query->orderBy('users.email', $sortDir);
            }

            return;
        }

        if (isset($directColumns[$sortBy])) {
            $query->join('applicant_profiles', 'applicant_profiles.id', '=', 'applications.applicant_id')
                ->select('applications.*')
                ->orderBy($directColumns[$sortBy], $sortDir);

            return;
        }

        $query->latest();
    }

    public function vacancySummary(Request $request): JsonResponse
    {
        $query = Vacancy::withCount('applications')
            ->with(['applications:id,vacancy_id,status']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('position_title', 'like', "%{$s}%")
                    ->orWhere('place_of_assignment', 'like', "%{$s}%")
                    ->orWhere('plantilla_no', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        match ($request->sort) {
            'deadline_desc' => $query->orderBy('deadline_at', 'desc'),
            'sg_desc' => $query->orderBy('salary_grade', 'desc'),
            'sg_asc' => $query->orderBy('salary_grade', 'asc'),
            'newest' => $query->orderBy('published_at', 'desc'),
            default => $query->orderByRaw("FIELD(status, 'published', 'draft', 'closed', 'filled', 'archived')")
                ->orderBy('created_at', 'desc'),
        };

        $perPage = min((int) ($request->per_page ?? 15), 100);

        $vacancies = $query->paginate($perPage)->withQueryString();

        $vacancies->getCollection()->transform(fn (Vacancy $v) => [
            'id'                  => $v->id,
            'position_title'      => $v->position_title,
            'salary_grade'        => $v->salary_grade,
            'monthly_salary'      => $v->monthly_salary,
            'place_of_assignment' => $v->place_of_assignment,
            'plantilla_no'        => $v->plantilla_no,
            'status'              => $v->status,
            'published_at'        => $v->published_at,
            'deadline_at'         => $v->deadline_at,
            'applications_count'  => $v->applications_count,
            'status_breakdown'    => $v->applications->groupBy('status')->map->count(),
        ]);

        return response()->json($this->paginatedResponse($vacancies));
    }

    /**
     * Normalize a LengthAwarePaginator into a {data, meta} shape — Laravel's
     * default JSON serialization puts pagination fields flat at the top
     * level (current_page, last_page, total, ...) rather than nested, which
     * doesn't match what the admin pages' frontend expects.
     */
    private function paginatedResponse($paginator): array
    {
        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
            ],
        ];
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

    public function withdraw(Request $request, Application $application): JsonResponse
    {
        $user    = $request->user();
        $profile = $user->applicantProfile;

        if (!$profile || $application->applicant_id !== $profile->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $nonWithdrawable = ['withdrawn', 'passed', 'failed', 'appointed', 'completed'];
        if (in_array($application->status, $nonWithdrawable)) {
            return response()->json(['message' => 'This application can no longer be withdrawn.'], 422);
        }

        $oldStatus = $application->status;
        $application->update(['status' => 'withdrawn', 'reviewed_at' => now()]);
        AuditLog::record("application_status_changed:{$oldStatus}→withdrawn", $application);

        return response()->json(['message' => 'Application withdrawn successfully.', 'data' => $application->fresh()]);
    }

    public function updateStatus(Request $request, Application $application): JsonResponse
    {
        $request->validate([
            'status'  => 'required|in:under_review,screened,qualified,disqualified,exam_scheduled,interviewed,shortlisted,for_interview,recommended,appointed,completed,withdrawn',
            'remarks' => 'nullable|string|max:1000',
            'reason'  => 'nullable|string|max:1000',
        ]);

        $oldStatus = $application->status;

        $updateData = [
            'status'      => $request->status,
            'remarks'     => $request->remarks ?? $request->reason,
            'reviewed_at' => now(),
        ];

        $application->update($updateData);

        AuditLog::record("application_status_changed:{$oldStatus}→{$request->status}", $application);

        // Notify the applicant via their user account
        $applicantUser = $application->applicant?->user;
        if ($applicantUser) {
            $applicantUser->notify(new ApplicationStatusUpdated($application, $oldStatus, $request->status));

            if ($request->status === 'shortlisted') {
                $applicantUser->notify(new ShortlistResult($application, true));
            } elseif ($request->status === 'disqualified') {
                $applicantUser->notify(new ShortlistResult($application, false));
            }
        }

        return response()->json([
            'message' => 'Application status updated successfully.',
            'data'    => $application->fresh(),
        ]);
    }
}
