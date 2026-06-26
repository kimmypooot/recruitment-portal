<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BackgroundCheck;
use App\Models\BackgroundInvestigationReport;
use App\Models\BeiRating;
use App\Models\DeliberationResult;
use App\Models\EoptResult;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\HrmbsboardComposition;
use App\Models\InterviewSchedule;
use App\Models\PreAssessment;
use App\Models\QsEvaluation;
use App\Models\Vacancy;
use App\Models\User;
use App\Services\AuditLog;
use App\Traits\FormatsApplicantName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HrmbsboardController extends Controller
{
    use FormatsApplicantName;

    public function compositions(): JsonResponse
    {
        $compositions = HrmbsboardComposition::with(['user:id,name,email,role', 'assignedBy:id,name'])
            ->orderBy('hrmpsb_role')
            ->get();

        return response()->json([
            'compositions' => $compositions,
            'roles' => HrmbsboardComposition::ROLES,
        ]);
    }

    public function assign(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'hrmpsb_role' => ['required', Rule::in(array_keys(HrmbsboardComposition::ROLES))],
            'member_type' => 'required|in:principal,alternate',
        ]);

        $composition = HrmbsboardComposition::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'hrmpsb_role' => $data['hrmpsb_role'],
            ],
            [
                'member_type' => $data['member_type'],
                'is_active' => true,
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ]
        );

        // Auto-set system role so the user lands on the HRMPSB portal on login
        $systemRole = $data['hrmpsb_role'] === 'secretariat'
            ? 'hrmpsb-secretariat'
            : 'hrmpsb-member';

        User::where('id', $data['user_id'])
            ->whereNotIn('role', ['admin', 'hr-manager'])
            ->update(['role' => $systemRole]);

        AuditLog::record("hrmpsb_assigned:{$data['hrmpsb_role']}", $composition);

        return response()->json($composition->load('user:id,name,email,role'), 201);
    }

    public function remove(HrmbsboardComposition $composition): JsonResponse
    {
        AuditLog::record("hrmpsb_removed:{$composition->hrmpsb_role}", $composition);

        $composition->delete();

        return response()->json(['message' => 'Member removed from HRMPSB.']);
    }

    public function toggleActive(HrmbsboardComposition $composition): JsonResponse
    {
        $composition->update(['is_active' => ! $composition->is_active]);

        AuditLog::record("hrmpsb_toggled_active:{$composition->hrmpsb_role}", $composition);

        return response()->json($composition->fresh());
    }

    public function toggleType(HrmbsboardComposition $composition): JsonResponse
    {
        $composition->update([
            'member_type' => $composition->member_type === 'principal' ? 'alternate' : 'principal',
        ]);

        AuditLog::record("hrmpsb_type_toggled:{$composition->hrmpsb_role}→{$composition->member_type}", $composition);

        return response()->json($composition->fresh());
    }

    public function myRole(Request $request): JsonResponse
    {
        $composition = HrmbsboardComposition::with('user:id,name,email')
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        return response()->json([
            'composition' => $composition,
            'roles' => HrmbsboardComposition::ROLES,
            'user' => $request->user()->only('id', 'name', 'email', 'role'),
        ]);
    }

    /**
     * Pipeline stage progress for one or more vacancies.
     * Used by the HRMPSB Dashboard to gate navigation buttons.
     */
    public function pipelineStages(Request $request): JsonResponse
    {
        $vacancyIds = $request->input('vacancy_ids', []);

        if (empty($vacancyIds)) {
            return response()->json([]);
        }

        $stages = [];

        foreach ($vacancyIds as $vacancyId) {
            $allAppIds = Application::where('vacancy_id', $vacancyId)->pluck('id');
            $appIds = Application::where('vacancy_id', $vacancyId)
                ->whereNotIn('status', ['withdrawn'])
                ->pluck('id');

            if ($allAppIds->isEmpty()) {
                $stages[$vacancyId] = [
                    'pre_assessment_exists' => false,
                    'qs_exists' => false,
                    'qs_locked' => false,
                    'twe_scheduled' => false,
                    'twe_exists' => false,
                    'cbwe_scheduled' => false,
                    'cbwe_exists' => false,
                    'bei_scheduled' => false,
                    'bei_exists' => false,
                    'bei_locked' => false,
                    'eopt_exists' => false,
                    'background_check_exists' => false,
                    'background_check_locked' => false,
                    'deliberation_exists' => false,
                ];

                continue;
            }

            $preAssessmentExists = PreAssessment::whereIn('application_id', $appIds)->exists();
            $qsExists = QsEvaluation::whereIn('application_id', $allAppIds)->exists();
            $qsLocked = QsEvaluation::whereIn('application_id', $allAppIds)->whereNotNull('locked_at')->exists();
            $tweScheduled = ExamSchedule::whereIn('application_id', $appIds)->where('exam_type', 'TWE')->exists();
            $tweExists = ExamResult::whereIn('application_id', $appIds)->where('exam_type', 'TWE')->exists();
            $cbweScheduled = ExamSchedule::whereIn('application_id', $appIds)->where('exam_type', 'CBWE')->exists();
            $cbweExists = ExamResult::whereIn('application_id', $appIds)->where('exam_type', 'CBWE')->exists();
            $beiScheduled = InterviewSchedule::whereIn('application_id', $appIds)->exists();
            $beiExists = BeiRating::whereIn('application_id', $appIds)->exists();
            $beiLocked = BeiRating::whereIn('application_id', $appIds)->whereNotNull('locked_at')->exists();
            $bgCheckExists = BackgroundCheck::whereIn('application_id', $appIds)->exists()
                || BackgroundInvestigationReport::whereIn('application_id', $appIds)->exists();
            $bgCheckLocked = BackgroundCheck::whereIn('application_id', $appIds)->whereNotNull('locked_at')->exists()
                || BackgroundInvestigationReport::whereIn('application_id', $appIds)->whereNotNull('submitted_at')->exists();
            $eoptExists = EoptResult::whereIn('application_id', $appIds)->exists();
            $deliExists = DeliberationResult::where('vacancy_id', $vacancyId)->exists();

            $stages[$vacancyId] = [
                'pre_assessment_exists' => $preAssessmentExists,
                'qs_exists' => $qsExists,
                'qs_locked' => $qsLocked,
                'twe_scheduled' => $tweScheduled,
                'twe_exists' => $tweExists,
                'cbwe_scheduled' => $cbweScheduled,
                'cbwe_exists' => $cbweExists,
                'bei_scheduled' => $beiScheduled,
                'bei_exists' => $beiExists,
                'bei_locked' => $beiLocked,
                'eopt_exists' => $eoptExists,
                'background_check_exists' => $bgCheckExists,
                'background_check_locked' => $bgCheckLocked,
                'deliberation_exists' => $deliExists,
            ];
        }

        return response()->json($stages);
    }

    /**
     * Applicant credentials for HRMPSB members.
     * Identity (name) is hidden unless the anonymization token has been unmasked.
     */
    public function applicantProfile(Request $request, Application $application): JsonResponse
    {
        $user = $request->user();

        $isAdmin = in_array($user->role, ['admin', 'hr-manager']);
        $isMember = HrmbsboardComposition::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if (! $isAdmin && ! $isMember) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $token = $application->anonymizationToken;
        $isUnmasked = $isAdmin || $token?->isUnmasked();

        $profile = $application->applicant->load([
            'educationalAttainments',
            'workExperiences',
            'trainings',
        ]);

        return response()->json([
            'application_id' => $application->id,
            'token' => $token?->token,
            'unmasked' => $isUnmasked,
            'name' => $isUnmasked ? $this->formatApplicantName($profile) : null,
            'eligibility' => $profile->eligibility,
            'education' => $profile->educationalAttainments,
            'experience' => $profile->workExperiences,
            'trainings' => $profile->trainings,
            'documents' => [
                'pds' => (bool) $profile->pds_path,
                'tor' => (bool) $profile->tor_path,
                'app_letter' => (bool) $profile->app_letter_path,
                'ipcr' => (bool) $profile->ipcr_path,
                'coe' => (bool) $profile->coe_path,
            ],
            'document_links' => [
                'pds' => $profile->pds_path ? "/api/hrmpsb/applications/{$application->id}/documents/pds" : null,
                'tor' => $profile->tor_path ? "/api/hrmpsb/applications/{$application->id}/documents/tor" : null,
                'app_letter' => $profile->app_letter_path ? "/api/hrmpsb/applications/{$application->id}/documents/app_letter" : null,
                'ipcr' => $profile->ipcr_path ? "/api/hrmpsb/applications/{$application->id}/documents/ipcr" : null,
                'coe' => $profile->coe_path ? "/api/hrmpsb/applications/{$application->id}/documents/coe" : null,
            ],
        ]);
    }

    /**
     * Stream an applicant document for HRMPSB members.
     * Uses a dedicated route so HR-only doc endpoints are not bypassed.
     */
    public function serveDocument(Request $request, Application $application, string $type)
    {
        $user = $request->user();

        $isAdmin = in_array($user->role, ['admin', 'hr-manager']);
        $isMember = HrmbsboardComposition::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if (! $isAdmin && ! $isMember) {
            abort(403);
        }

        $pathMap = [
            'pds' => 'pds_path',
            'tor' => 'tor_path',
            'app_letter' => 'app_letter_path',
            'ipcr' => 'ipcr_path',
            'coe' => 'coe_path',
        ];

        abort_if(! isset($pathMap[$type]), 404);

        $profile = $application->applicant;
        $path = $profile?->{$pathMap[$type]};

        abort_if(! $path || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    /**
     * All applicants for a vacancy with their document statuses and links.
     */
    public function vacancyApplicants(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $isAdmin = in_array($user->role, ['admin', 'hr-manager']);
        $isMember = HrmbsboardComposition::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if (! $isAdmin && ! $isMember) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $applications = $vacancy->applications()
            ->with(['applicant.user:id,name'])
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($app) {
                $profile = $app->applicant;

                $docExists = fn ($col) => $profile?->$col && Storage::disk('public')->exists($profile->$col);
                $docLink   = fn ($col, $type) => $docExists($col) ? "/api/hrmpsb/applications/{$app->id}/documents/{$type}" : null;

                return [
                    'id'              => $app->id,
                    'anonymized_name' => $app->anonymizationToken?->token ?? ('APP-' . $app->id),
                    'status'          => $app->status,
                    'submitted_at'    => $app->submitted_at,
                    'documents'       => [
                        'pds'        => $docExists('pds_path'),
                        'tor'        => $docExists('tor_path'),
                        'app_letter' => $docExists('app_letter_path'),
                        'ipcr'       => $docExists('ipcr_path'),
                        'coe'        => $docExists('coe_path'),
                    ],
                    'document_links'  => [
                        'pds'        => $docLink('pds_path', 'pds'),
                        'tor'        => $docLink('tor_path', 'tor'),
                        'app_letter' => $docLink('app_letter_path', 'app_letter'),
                        'ipcr'       => $docLink('ipcr_path', 'ipcr'),
                        'coe'        => $docLink('coe_path', 'coe'),
                    ],
                ];
            });

        return response()->json([
            'vacancy' => [
                'id'               => $vacancy->id,
                'position_title'   => $vacancy->position_title,
                'plantilla_no'     => $vacancy->plantilla_no,
                'salary_grade'     => $vacancy->salary_grade,
                'place_of_assignment' => $vacancy->place_of_assignment,
            ],
            'applicants' => $applications,
        ]);
    }
}
