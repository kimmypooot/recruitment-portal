<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BackgroundInvestigationReport;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Notifications\BackgroundInvestigationRequest;
use App\Services\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Sanctum\PersonalAccessToken;

class BackgroundInvestigationController extends Controller
{
    private function isSecretariat(int $userId): bool
    {
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('hrmpsb_role', 'secretariat')
            ->where('is_active', true)
            ->exists();
    }

    private function isAuthorized(int $userId): bool
    {
        $user = \App\Models\User::find($userId);
        if ($user?->canAccessAdminModule()) {
            return true;
        }

        return HrmbsboardComposition::where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (!$this->isAuthorized($user->id)) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $isSecretariat = $this->isSecretariat($user->id) || $user->canAccessAdminModule();

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->with(['applicant:id,user_id', 'applicant.user:id,first_name,last_name,middle_name,suffix', 'backgroundInvestigationReports'])
            ->orderBy('id')
            ->get();

        $locked = BackgroundInvestigationReport::whereIn('application_id', $applications->pluck('id'))
            ->whereNotNull('locked_at')
            ->exists();

        return response()->json([
            'vacancy'       => $vacancy->only(
                'id', 'position_title', 'plantilla_no', 'salary_grade',
                'place_of_assignment', 'status', 'published_at'
            ),
            'applications'  => $applications,
            'is_secretariat' => $isSecretariat,
            'locked'        => $locked,
        ]);
    }

public function generateLink(Request $request): JsonResponse
        {
            $user = $request->user();

            if (! $this->isSecretariat($user->id) && ! $user->canAccessAdminModule()) {
                return response()->json(['message' => 'Only the HRMPSB Secretariat or an admin-level user can generate upload links.'], 403);
            }

            $data = $request->validate([
                'application_id'    => 'required|exists:applications,id',
                'investigator_name' => 'required|string|max:255',
                'investigator_email'=> 'required|email|max:255',
            ]);

        $application = Application::with('applicant.user', 'vacancy')->findOrFail($data['application_id']);

        $existing = BackgroundInvestigationReport::where('application_id', $application->id)
            ->whereNull('submitted_at')
            ->where(function ($q) {
                $q->whereNull('token_expires_at')->orWhere('token_expires_at', '>', now());
            })
            ->first();

        if ($existing) {
            return response()->json(['message' => 'An active upload link already exists for this application. Revoke it first or wait for submission.'], 422);
        }

        // Clean up any expired un-submitted reports
        BackgroundInvestigationReport::where('application_id', $application->id)
            ->whereNull('submitted_at')
            ->delete();

        $report = BackgroundInvestigationReport::create([
            'application_id'    => $application->id,
            'investigator_name' => $data['investigator_name'],
            'investigator_email'=> $data['investigator_email'],
            'token'             => Str::random(64),
            'token_expires_at'  => now()->addDays(30),
        ]);

        $profile = $application->applicant;
        $mi = $profile->middle_name
            ? ' ' . strtoupper(substr($profile->middle_name, 0, 1)) . '.'
            : '';
        $applicantName = "{$profile->last_name}, {$profile->first_name}{$mi}";

        Notification::route('mail', $report->investigator_email)
            ->notify(new BackgroundInvestigationRequest(
                $report,
                $application->vacancy->position_title,
                $applicantName,
            ));

        AuditLog::record('background_investigation_link_generated', $report);

        return response()->json([
            'message' => 'Upload link generated and sent to the investigator.',
            'report'  => $report,
        ], 201);
    }

public function resendLink(Request $request, BackgroundInvestigationReport $report): JsonResponse
        {
            $user = $request->user();

            if (! $this->isSecretariat($user->id) && ! $user->canAccessAdminModule()) {
                return response()->json(['message' => 'Only the HRMPSB Secretariat or an admin-level user can resend upload links.'], 403);
            }

        if ($report->isSubmitted()) {
            return response()->json(['message' => 'This report has already been submitted. Cannot resend.'], 422);
        }

        $report->update([
            'token'            => Str::random(64),
            'token_expires_at' => now()->addDays(30),
        ]);

        $application = $report->application()->with('applicant.user', 'vacancy')->first();

        $profile = $application->applicant;
        $mi = $profile->middle_name
            ? ' ' . strtoupper(substr($profile->middle_name, 0, 1)) . '.'
            : '';
        $applicantName = "{$profile->last_name}, {$profile->first_name}{$mi}";

        Notification::route('mail', $report->investigator_email)
            ->notify(new BackgroundInvestigationRequest(
                $report->fresh(),
                $application->vacancy->position_title,
                $applicantName,
            ));

        AuditLog::record('background_investigation_link_resent', $report);

        return response()->json([
            'message' => 'Upload link resent to the investigator.',
        ]);
    }

public function revokeLink(Request $request, BackgroundInvestigationReport $report): JsonResponse
        {
            $user = $request->user();

            if (! $this->isSecretariat($user->id) && ! $user->canAccessAdminModule()) {
                return response()->json(['message' => 'Only the HRMPSB Secretariat or an admin-level user can revoke upload links.'], 403);
            }

        if ($report->isSubmitted()) {
            return response()->json(['message' => 'Cannot revoke a submitted report.'], 422);
        }

        AuditLog::record('background_investigation_link_revoked', $report);

        $report->delete();

        return response()->json(['message' => 'Upload link revoked. You can now generate a new one.']);
    }

    public function showUploadForm(string $token): Response|JsonResponse
    {
        $report = BackgroundInvestigationReport::with('application.applicant.user', 'application.vacancy')
            ->where('token', $token)
            ->first();

        if (!$report) {
            return Inertia::render('Public/BackgroundInvestigationUpload', [
                'error' => 'Invalid or expired upload link.',
            ]);
        }

        if ($report->isSubmitted()) {
            return Inertia::render('Public/BackgroundInvestigationUpload', [
                'error' => 'This report has already been submitted.',
            ]);
        }

        if ($report->isExpired()) {
            return Inertia::render('Public/BackgroundInvestigationUpload', [
                'error' => 'This upload link has expired. Please contact the HRMPSB Secretariat for a new link.',
            ]);
        }

        $profile = $report->application->applicant;
        $mi = $profile->middle_name
            ? ' ' . strtoupper(substr($profile->middle_name, 0, 1)) . '.'
            : '';
        $applicantName = "{$profile->last_name}, {$profile->first_name}{$mi}";

        $vacancy = $report->application->vacancy;

        return Inertia::render('Public/BackgroundInvestigationUpload', [
            'token'              => $token,
            'investigatorName'   => $report->investigator_name,
            'applicantName'      => $applicantName,
            'positionTitle'      => $vacancy->position_title,
            'itemNumber'         => $vacancy->plantilla_no,
            'salaryGrade'        => $vacancy->salary_grade,
            'placeOfAssignment'  => $vacancy->place_of_assignment,
        ]);
    }

    public function upload(Request $request, string $token): JsonResponse
    {
        $report = BackgroundInvestigationReport::where('token', $token)->first();

        if (!$report) {
            return response()->json(['message' => 'Invalid upload link.'], 404);
        }

        if ($report->isSubmitted()) {
            return response()->json(['message' => 'This report has already been submitted.'], 422);
        }

        if ($report->isExpired()) {
            return response()->json(['message' => 'This upload link has expired.'], 422);
        }

        $data = $request->validate([
            'on_competencies' => 'required|string|min:1000',
            'on_performance'  => 'required|string|min:1000',
            'file'            => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $request->file('file')->store('background-investigations', 'local');

        $report->update([
            'file_path'         => $path,
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'on_competencies'   => $data['on_competencies'],
            'on_performance'    => $data['on_performance'],
            'submitted_at'      => now(),
        ]);

        AuditLog::record('background_investigation_submitted', $report);

        return response()->json([
            'message' => 'Background investigation report submitted successfully.',
        ]);
    }

    public function viewPdf(Request $request, BackgroundInvestigationReport $report)
    {
        $token = $request->query('token');
        $user = null;

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $user = $accessToken->tokenable;
            }
        }

        if (!$user || !$this->isAuthorized($user->id)) {
            abort(403, 'Access denied.');
        }

        if (!$report->isSubmitted() || !$report->file_path) {
            return response()->json(['message' => 'No file uploaded yet.'], 404);
        }

        if (!Storage::disk('local')->exists($report->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->response($report->file_path, $report->original_filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
