<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\HrmbsboardComposition;
use App\Models\PreAssessment;
use App\Models\QsEvaluation;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreAssessmentController extends Controller
{
    use \App\Traits\FormatsApplicantName;

    public function index(Vacancy $vacancy): JsonResponse
    {
        $applications = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn'])
            ->with([
                'applicant',
                'applicant.educationalAttainments',
                'applicant.workExperiences',
                'applicant.trainings',
                'preAssessment',
            ])
            ->orderBy('created_at')
            ->get();

        $appIds = $applications->pluck('id');

        $qsEvals = QsEvaluation::whereIn('application_id', $appIds)
            ->with('evaluator:id,name')
            ->get()
            ->groupBy('application_id');

        $members = HrmbsboardComposition::with('user:id,name')
            ->where('is_active', true)
            ->where('hrmpsb_role', '!=', 'secretariat')
            ->orderBy('hrmpsb_role')
            ->get();

        $rows = $applications->map(function (Application $app) use ($qsEvals) {
            $profile = $app->applicant;

            return [
                'id'            => $app->id,
                'applicant_no'  => 'APP-' . date('Y', strtotime($app->created_at)) . '-' . str_pad($app->id, 5, '0', STR_PAD_LEFT),
                'status'        => $app->status,
                'applicant'     => [
                    'full_name'   => $this->formatApplicantName($profile),
                    'has_pds'     => (bool) $profile?->pds_path,
                    'has_ipcr'    => (bool) $profile?->ipcr_path,
                    'has_tor'     => (bool) $profile?->tor_path,
                    'has_coe'     => (bool) $profile?->coe_path,
                    'pds_url'     => $profile?->pds_path
                        ? "/api/hrmpsb/applications/{$app->id}/documents/pds"
                        : null,
                    'eligibility' => $profile?->eligibility,
                    'education'   => ($profile?->educationalAttainments ?? collect())->map(fn ($e) => [
                        'level'          => $e->level,
                        'degree_course'  => $e->degree_course,
                        'school_name'    => $e->school_name,
                        'period_from'    => $e->period_from,
                        'period_to'      => $e->period_to,
                        'year_graduated' => $e->year_graduated,
                        'honors'         => $e->honors,
                    ])->values(),
                    'experience'  => ($profile?->workExperiences ?? collect())->map(fn ($w) => [
                        'position_title'     => $w->position_title,
                        'department_agency'  => $w->department_agency,
                        'date_from'          => $w->date_from,
                        'date_to'            => $w->date_to,
                        'is_present'         => $w->is_present,
                        'government_service' => $w->government_service,
                        'appointment_status' => $w->appointment_status,
                    ])->values(),
                    'trainings'   => ($profile?->trainings ?? collect())->map(fn ($t) => [
                        'title'        => $t->title,
                        'hours'        => $t->hours,
                        'date_from'    => $t->date_from,
                        'date_to'      => $t->date_to,
                        'conducted_by' => $t->conducted_by,
                        'ld_type'      => $t->ld_type,
                    ])->values(),
                ],
                'pre_assessment' => $app->preAssessment,
                'qs_evaluations' => ($qsEvals[$app->id] ?? collect())->map(fn ($e) => [
                    'id'                => $e->id,
                    'evaluator_id'      => $e->evaluator_id,
                    'evaluator_name'    => $e->evaluator?->name,
                    'overall_qualified' => $e->overall_qualified,
                    'education_meets'   => $e->education_meets,
                    'experience_meets'  => $e->experience_meets,
                    'training_meets'    => $e->training_meets,
                    'eligibility_meets' => $e->eligibility_meets,
                    'remarks'           => $e->remarks,
                ])->values(),
            ];
        });

        return response()->json([
            'vacancy' => [
                'id'                 => $vacancy->id,
                'position_title'     => $vacancy->position_title,
                'item_number'        => $vacancy->item_number,
                'salary_grade'       => $vacancy->salary_grade,
                'place_of_assignment'=> $vacancy->place_of_assignment,
                'education_req'      => $vacancy->education_req,
                'experience_req'     => $vacancy->experience_req,
                'training_req'       => $vacancy->training_req,
                'eligibility_req'    => $vacancy->eligibility_req,
            ],
            'applications'    => $rows,
            'hrmpsb_members'  => $members->map(fn ($m) => [
                'user_id' => $m->user_id,
                'name'    => $m->user?->name ?? '—',
                'role'    => HrmbsboardComposition::ROLES[$m->hrmpsb_role] ?? $m->hrmpsb_role,
            ]),
        ]);
    }

    public function upsert(Request $request, Application $application): JsonResponse
    {
        $data = $request->validate([
            'pds_submitted'         => 'nullable|boolean',
            'ipcr_submitted'        => 'nullable|boolean',
            'tor_submitted'         => 'nullable|boolean',
            'coe_submitted'         => 'nullable|boolean',
            'pds_remarks'           => 'nullable|string|max:2000',
            'ipcr_remarks'          => 'nullable|string|max:2000',
            'tor_remarks'           => 'nullable|string|max:2000',
            'coe_remarks'           => 'nullable|string|max:2000',
            'requirements_complete' => 'nullable|boolean',
            'requirements_remarks'  => 'nullable|string|max:2000',
            'secretariat_remarks'   => 'nullable|string|max:2000',
            'license_remarks'       => 'nullable|string|max:2000',
            'hrd_assessment'        => 'nullable|boolean',
            'hrd_remarks'           => 'nullable|string|max:2000',
            'consensus'             => 'nullable|boolean',
        ]);

        if ($data['requirements_complete'] === false && empty($data['requirements_remarks'])) {
            return response()->json([
                'message' => 'Remarks are required when requirements are incomplete.',
                'errors'  => ['requirements_remarks' => ['Remarks are required when requirements are incomplete.']],
            ], 422);
        }

        $record = PreAssessment::updateOrCreate(
            ['application_id' => $application->id],
            array_merge($data, [
                'assessed_by' => $request->user()->id,
                'assessed_at' => now(),
            ])
        );

        return response()->json($record);
    }
}
