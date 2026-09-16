<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\AppointingAuthorityDecision;
use App\Models\BackgroundCheck;
use App\Models\BackgroundInvestigationReport;
use App\Models\BeiRating;
use App\Models\CbweRating;
use App\Models\ComparativeAssessmentResult;
use App\Models\DeliberationResult;
use App\Models\EoptResult;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\HrmpsbComposition;
use App\Models\InterviewSchedule;
use App\Models\PlaceOfAssignmentHead;
use App\Models\PreAssessment;
use App\Models\QsEvaluation;
use App\Models\Vacancy;
use App\Services\AuditLog;
use App\Services\HrmpsbCompositionService;
use App\Traits\FormatsApplicantName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use ZipArchive;

class HrmpsbController extends Controller
{
    use FormatsApplicantName;

    /** Applicant document types the board can see, mapped to their profile column. */
    private const DOCUMENT_COLUMNS = [
        'pds' => 'pds_path',
        'tor' => 'tor_path',
        'app_letter' => 'app_letter_path',
        'ipcr' => 'ipcr_path',
        'coe' => 'coe_path',
    ];

    /** Filenames used for the same documents inside the requirements archive. */
    private const DOCUMENT_LABELS = [
        'pds' => 'PDS',
        'tor' => 'TOR',
        'app_letter' => 'App_Letter',
        'ipcr' => 'IPCR',
        'coe' => 'COE',
    ];

    private function isActiveBoardMember(int $userId): bool
    {
        return HrmpsbComposition::where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    public function compositions(Request $request): JsonResponse
    {
        $vacancyId = $request->query('vacancy_id');

        if ($vacancyId) {
            $vacancy = Vacancy::findOrFail($vacancyId);
            $service = app(HrmpsbCompositionService::class);
            $compositions = $service->forVacancy($vacancy);

            $compositions = $compositions->map(fn ($c) => [
                'id' => $c->id ?? null,
                'user_id' => $c->user_id,
                'hrmpsb_role' => $c->hrmpsb_role,
                'member_type' => $c->member_type ?? 'principal',
                'is_active' => $c->is_active ?? true,
                'is_dynamic' => $c->is_dynamic ?? false,
                'assigned_by' => $c->assignedBy ?? null,
                'assigned_at' => $c->assigned_at ?? null,
                'user' => $c->user ? [
                    'id' => $c->user->id,
                    'first_name' => $c->user->first_name,
                    'last_name' => $c->user->last_name,
                    'middle_name' => $c->user->middle_name,
                    'suffix' => $c->user->suffix,
                    'email' => $c->user->email,
                    'full_name' => trim(implode(' ', array_filter([
                        $c->user->first_name,
                        $c->user->middle_name,
                        $c->user->last_name,
                        $c->user->suffix,
                    ]))),
                ] : null,
            ]);

            return response()->json([
                'compositions' => $compositions,
                'roles' => HrmpsbComposition::ALL_ROLES,
            ]);
        }

        $compositions = HrmpsbComposition::with(['user:id,first_name,last_name,middle_name,suffix,email,role', 'assignedBy:id,first_name,last_name,middle_name,suffix'])
            ->orderBy('hrmpsb_role')
            ->get();

        return response()->json([
            'compositions' => $compositions,
            'roles' => HrmpsbComposition::ROLES,
        ]);
    }

    public function assign(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'hrmpsb')],
            'hrmpsb_role' => ['required', Rule::in(array_keys(HrmpsbComposition::ROLES))],
            'member_type' => 'required|in:principal,alternate',
        ]);

        $composition = HrmpsbComposition::updateOrCreate(
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

        AuditLog::record("hrmpsb_assigned:{$data['hrmpsb_role']}", $composition);

        return response()->json($composition->load('user:id,first_name,last_name,middle_name,suffix,email,role'), 201);
    }

    public function remove(HrmpsbComposition $composition): JsonResponse
    {
        AuditLog::record("hrmpsb_removed:{$composition->hrmpsb_role}", $composition);

        $composition->delete();

        return response()->json(['message' => 'Member removed from HRMPSB.']);
    }

    public function toggleActive(HrmpsbComposition $composition): JsonResponse
    {
        $composition->update(['is_active' => ! $composition->is_active]);

        AuditLog::record("hrmpsb_toggled_active:{$composition->hrmpsb_role}", $composition);

        return response()->json($composition->fresh());
    }

    public function toggleType(HrmpsbComposition $composition): JsonResponse
    {
        $composition->update([
            'member_type' => $composition->member_type === 'principal' ? 'alternate' : 'principal',
        ]);

        AuditLog::record("hrmpsb_type_toggled:{$composition->hrmpsb_role}→{$composition->member_type}", $composition);

        return response()->json($composition->fresh());
    }

    public function myRole(Request $request): JsonResponse
    {
        $composition = HrmpsbComposition::with('user:id,first_name,last_name,middle_name,suffix,email')
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();

        return response()->json([
            'composition' => $composition,
            'roles' => HrmpsbComposition::ROLES,
            'user' => [
                'id' => $request->user()->id,
                'first_name' => $request->user()->first_name,
                'last_name' => $request->user()->last_name,
                'full_name' => $request->user()->full_name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
            ],
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

        $emptyStageFlags = [
            'pre_assessment_exists' => false,
            'qs_exists' => false,
            'qs_locked' => false,
            'twe_scheduled' => false,
            'twe_exists' => false,
            'cbwe_scheduled' => false,
            'cbwe_exists' => false,
            'cbwe_locked' => false,
            'bei_scheduled' => false,
            'bei_exists' => false,
            'bei_locked' => false,
            'eopt_exists' => false,
            'background_check_exists' => false,
            'background_check_locked' => false,
            'deliberation_exists' => false,
            'comparative_assessment_exists' => false,
            'appointing_authority_exists' => false,
            'appointing_authority_appointed' => false,
        ];

        // Previously this issued ~17 queries per vacancy (a 30-vacancy
        // dashboard load meant 500+ queries). One query per application-
        // keyed table across every requested vacancy, then grouped in PHP,
        // replaces that with a small constant number of queries regardless
        // of how many vacancies are asked for. qs_exists/qs_locked
        // intentionally consider every application id including withdrawn
        // ones (matching PipelineStageService's convention); every other
        // flag excludes withdrawn applications.
        $applications = Application::whereIn('vacancy_id', $vacancyIds)->get(['id', 'vacancy_id', 'status']);
        $vacancyIdOf = $applications->pluck('vacancy_id', 'id');
        $allAppIds = $applications->pluck('id');
        $appIds = $applications->where('status', '!=', 'withdrawn')->pluck('id');
        // Grouped from ALL applications (withdrawn included) so the "does this
        // vacancy have any applications at all" check below matches the
        // original $allAppIds->isEmpty() early-return exactly.
        $allAppIdsByVacancy = $applications->groupBy('vacancy_id');

        $groupByVacancy = fn ($rows) => $rows->groupBy(fn ($row) => $vacancyIdOf[$row->application_id] ?? null);

        $preAssessments = $groupByVacancy(PreAssessment::whereIn('application_id', $appIds)->get(['application_id']));
        $qsEvaluations = $groupByVacancy(QsEvaluation::whereIn('application_id', $allAppIds)->get(['application_id', 'locked_at']));
        $tweSchedules = $groupByVacancy(ExamSchedule::whereIn('application_id', $appIds)->where('exam_type', 'TWE')->get(['application_id']));
        $tweResults = $groupByVacancy(ExamResult::whereIn('application_id', $appIds)->where('exam_type', 'TWE')->get(['application_id']));
        $cbweSchedules = $groupByVacancy(ExamSchedule::whereIn('application_id', $appIds)->where('exam_type', 'CBWE')->get(['application_id']));
        $cbweRatings = $groupByVacancy(CbweRating::whereIn('application_id', $appIds)->get(['application_id', 'locked_at']));
        $beiSchedules = $groupByVacancy(InterviewSchedule::whereIn('application_id', $appIds)->get(['application_id']));
        $beiRatings = $groupByVacancy(BeiRating::whereIn('application_id', $appIds)->get(['application_id', 'locked_at']));
        $backgroundChecks = $groupByVacancy(BackgroundCheck::whereIn('application_id', $appIds)->get(['application_id', 'locked_at']));
        $bgReports = $groupByVacancy(BackgroundInvestigationReport::whereIn('application_id', $appIds)->get(['application_id', 'locked_at']));
        $eoptResults = $groupByVacancy(EoptResult::whereIn('application_id', $appIds)->get(['application_id']));

        $deliberations = DeliberationResult::whereIn('vacancy_id', $vacancyIds)->whereNotNull('locked_at')->get(['vacancy_id'])->groupBy('vacancy_id');
        $comparativeAssessments = ComparativeAssessmentResult::whereIn('vacancy_id', $vacancyIds)->get(['vacancy_id'])->groupBy('vacancy_id');
        $aaDecisions = AppointingAuthorityDecision::whereIn('vacancy_id', $vacancyIds)->get(['vacancy_id', 'action'])->groupBy('vacancy_id');

        $empty = collect();
        $stages = [];

        foreach ($vacancyIds as $vacancyId) {
            if (($allAppIdsByVacancy->get($vacancyId) ?? $empty)->isEmpty()) {
                $stages[$vacancyId] = $emptyStageFlags;

                continue;
            }

            $qsRows = $qsEvaluations->get($vacancyId, $empty);
            $cbweRows = $cbweRatings->get($vacancyId, $empty);
            $beiRows = $beiRatings->get($vacancyId, $empty);
            $bgCheckRows = $backgroundChecks->get($vacancyId, $empty);
            $bgReportRows = $bgReports->get($vacancyId, $empty);
            $aaRows = $aaDecisions->get($vacancyId, $empty);

            $stages[$vacancyId] = [
                'pre_assessment_exists' => $preAssessments->get($vacancyId, $empty)->isNotEmpty(),
                'qs_exists' => $qsRows->isNotEmpty(),
                'qs_locked' => $qsRows->whereNotNull('locked_at')->isNotEmpty(),
                'twe_scheduled' => $tweSchedules->get($vacancyId, $empty)->isNotEmpty(),
                'twe_exists' => $tweResults->get($vacancyId, $empty)->isNotEmpty(),
                'cbwe_scheduled' => $cbweSchedules->get($vacancyId, $empty)->isNotEmpty(),
                'cbwe_exists' => $cbweRows->isNotEmpty(),
                'cbwe_locked' => $cbweRows->whereNotNull('locked_at')->isNotEmpty(),
                'bei_scheduled' => $beiSchedules->get($vacancyId, $empty)->isNotEmpty(),
                'bei_exists' => $beiRows->isNotEmpty(),
                'bei_locked' => $beiRows->whereNotNull('locked_at')->isNotEmpty(),
                'eopt_exists' => $eoptResults->get($vacancyId, $empty)->isNotEmpty(),
                'background_check_exists' => $bgCheckRows->isNotEmpty() || $bgReportRows->isNotEmpty(),
                'background_check_locked' => $bgCheckRows->whereNotNull('locked_at')->isNotEmpty() || $bgReportRows->whereNotNull('locked_at')->isNotEmpty(),
                'deliberation_exists' => $deliberations->has($vacancyId),
                'comparative_assessment_exists' => $comparativeAssessments->has($vacancyId),
                'appointing_authority_exists' => $aaRows->isNotEmpty(),
                'appointing_authority_appointed' => $aaRows->where('action', 'appointed')->isNotEmpty(),
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

        $isAdmin = $user->canAccessAdminModule();

        if (! $isAdmin && ! $this->isActiveBoardMember($user->id)) {
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
            'documents' => collect(self::DOCUMENT_COLUMNS)
                ->map(fn (string $column) => (bool) $profile->{$column})
                ->all(),
            // The documents themselves (PDS especially — full name, photo,
            // address on its face) are as identifying as `name` above, so
            // they're withheld under the same $isUnmasked gate rather than
            // being handed out unconditionally while identity is supposed
            // to be blind. `documents` above (existence only) stays
            // unconditional — that alone doesn't identify anyone, and
            // completeness-checking is a legitimate pre-unmask need.
            'document_links' => collect(self::DOCUMENT_COLUMNS)
                ->map(fn (string $column, string $type) => $isUnmasked && $profile->{$column}
                    ? "/api/hrmpsb/applications/{$application->id}/documents/{$type}"
                    : null)
                ->all(),
        ]);
    }

    /**
     * Stream an applicant document for HRMPSB members.
     * Uses a dedicated route so HR-only doc endpoints are not bypassed.
     */
    public function serveDocument(Request $request, Application $application, string $type)
    {
        $user = $request->user();

        $isAdmin = $user->canAccessAdminModule();

        if (! $isAdmin && ! $this->isActiveBoardMember($user->id)) {
            abort(403);
        }

        // Defense in depth for the same rule applicantProfile() enforces on
        // document_links: while identity is masked, the underlying files
        // (PDS especially — full name, photo, address on its face) must not
        // be reachable either, even by a board member hitting this route
        // directly with a known application id rather than the link the
        // frontend would have withheld.
        $token = $application->anonymizationToken;
        $isUnmasked = $isAdmin || $token?->isUnmasked();
        if (! $isUnmasked) {
            abort(403, 'Applicant identity is masked until deliberation unmasking.');
        }

        abort_if(! isset(self::DOCUMENT_COLUMNS[$type]), 404);

        $profile = $application->applicant;
        $path = $profile?->{self::DOCUMENT_COLUMNS[$type]};

        abort_if(! $path || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    /**
     * All applicants for a vacancy with their document statuses and links.
     */
    public function vacancyApplicants(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (! $user->canAccessAdminModule() && ! $this->isActiveBoardMember($user->id)) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $applications = $vacancy->applications()
            ->with(['applicant:id,user_id,pds_path,app_letter_path,ipcr_path,coe_path,tor_path', 'applicant.user:id,first_name,last_name,middle_name,suffix'])
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($app) {
                $profile = $app->applicant;

                $docExists = fn (string $column) => $profile?->{$column} && Storage::disk('local')->exists($profile->{$column});

                return [
                    'id' => $app->id,
                    'anonymized_name' => $app->anonymizationToken?->token ?? ('APP-'.$app->id),
                    'status' => $app->status,
                    'submitted_at' => $app->submitted_at,
                    'documents' => collect(self::DOCUMENT_COLUMNS)
                        ->map(fn (string $column) => $docExists($column))
                        ->all(),
                    'document_links' => collect(self::DOCUMENT_COLUMNS)
                        ->map(fn (string $column, string $type) => $docExists($column)
                            ? "/api/hrmpsb/applications/{$app->id}/documents/{$type}"
                            : null)
                        ->all(),
                ];
            });

        return response()->json([
            'vacancy' => [
                'id' => $vacancy->id,
                'position_title' => $vacancy->position_title,
                'plantilla_no' => $vacancy->plantilla_no,
                'salary_grade' => $vacancy->salary_grade,
                'place_of_assignment' => $vacancy->place_of_assignment,
            ],
            'applicants' => $applications,
        ]);
    }

    // ── Place of Assignment Heads ─────────────────────────────────────────

    public function poaHeads(): JsonResponse
    {
        $heads = PlaceOfAssignmentHead::with(['user:id,first_name,last_name,middle_name,suffix,email,role', 'assignedBy:id,first_name,last_name,middle_name,suffix'])
            ->orderBy('place_of_assignment')
            ->get();

        return response()->json(['heads' => $heads]);
    }

    public function assignPoaHead(Request $request): JsonResponse
    {
        $data = $request->validate([
            'place_of_assignment' => ['required', Rule::in(Vacancy::placesOfAssignment())],
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'hrmpsb')],
        ]);

        $head = PlaceOfAssignmentHead::updateOrCreate(
            ['place_of_assignment' => $data['place_of_assignment']],
            [
                'user_id' => $data['user_id'],
                'assigned_by' => $request->user()->id,
            ]
        );

        AuditLog::record("poa_head_assigned:{$data['place_of_assignment']}", $head);

        return response()->json($head->load('user:id,first_name,last_name,middle_name,suffix,email,role'), 201);
    }

    public function removePoaHead(PlaceOfAssignmentHead $head): JsonResponse
    {
        AuditLog::record("poa_head_removed:{$head->place_of_assignment}", $head);

        $head->delete();

        return response()->json(['message' => 'Head of unit removed for this place of assignment.']);
    }

    public function downloadRequirements(Request $request, Vacancy $vacancy)
    {
        $user = $request->user();

        if (! $user->canAccessAdminModule() && ! $this->isActiveBoardMember($user->id)) {
            abort(403);
        }

        $documentTypes = array_keys(self::DOCUMENT_COLUMNS);

        $data = $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'integer|exists:applications,id',
            'document_type' => ['required', Rule::in([...$documentTypes, 'all'])],
        ]);

        $docTypes = $data['document_type'] === 'all'
            ? $documentTypes
            : [$data['document_type']];

        $zip = new ZipArchive;
        $tmpPath = tempnam(sys_get_temp_dir(), 'req_');
        $zipName = 'Requirements_'.now()->format('Y-m-d_His').'.zip';

        if ($zip->open($tmpPath, ZipArchive::CREATE) !== true) {
            return response()->json(['message' => 'Failed to create archive.'], 500);
        }

        $added = 0;

        foreach ($data['application_ids'] as $appId) {
            $application = Application::with('applicant')->find($appId);
            if (! $application || ! $application->applicant) {
                continue;
            }

            $profile = $application->applicant;
            $folderName = 'APP-'.$appId;

            foreach ($docTypes as $type) {
                $path = $profile->{self::DOCUMENT_COLUMNS[$type]};

                if (! $path || ! Storage::disk('local')->exists($path)) {
                    continue;
                }

                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $fileName = $folderName.'/'.self::DOCUMENT_LABELS[$type].'.'.$ext;
                $fullPath = Storage::disk('local')->path($path);

                if ($zip->addFile($fullPath, $fileName)) {
                    $added++;
                }
            }
        }

        $zip->close();

        if ($added === 0) {
            unlink($tmpPath);

            return response()->json(['message' => 'No documents found for the selected applicants and document type.'], 404);
        }

        return response()->download($tmpPath, $zipName)->deleteFileAfterSend(true);
    }
}
