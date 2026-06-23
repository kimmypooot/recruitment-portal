<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\HrmbsboardComposition;
use App\Models\QsEvaluation;
use App\Models\Vacancy;
use App\Services\AnonymizationService;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QsEvaluationController extends Controller
{
    private function getComposition(int $userId): ?HrmbsboardComposition
    {
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }

    private function isSecretariat(int $userId): bool
    {
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('hrmpsb_role', 'secretariat')
            ->where('is_active', true)
            ->exists();
    }

    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'hr-manager', 'hrmpsb-secretariat'])) {
            $composition = $this->getComposition($user->id);
            if (!$composition) {
                return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
            }
        }

        $isSecretariat = $this->isSecretariat($user->id)
            || in_array($user->role, ['admin', 'hr-manager']);

<<<<<<< HEAD
        // Resolve the active secretariat user so members can reference their evaluation
        $secretariatComp = HrmbsboardComposition::where('hrmpsb_role', 'secretariat')
            ->where('is_active', true)
            ->first();

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->with(['applicant:id,first_name,last_name,middle_name'])
            ->withCount('documents')
            ->orderBy('id')
            ->get();

        $seq = 1;

        $applications->each(function ($app) use ($user, $isSecretariat, &$seq, $secretariatComp) {
            $app->display_code = 'APP-' . str_pad($seq++, 3, '0', STR_PAD_LEFT);

            // Secretariat's evaluation — visible to all as the initial reference
            $secretariatEval = $secretariatComp
                ? QsEvaluation::where('application_id', $app->id)
                    ->where('evaluator_id', $secretariatComp->user_id)
                    ->first()
                : null;

            $app->secretariat_evaluation = $secretariatEval;
            $app->secretariat_evaluated  = $secretariatEval !== null;

            // Everyone gets their own evaluation record
            $app->my_evaluation = QsEvaluation::where('application_id', $app->id)
                ->where('evaluator_id', $user->id)
                ->first();

            if ($isSecretariat) {
                // Secretariat additionally sees all member evaluations consolidated
=======
        $applications = Application::where('vacancy_id', $vacancy->id)
            ->with(['applicant:id,first_name,last_name'])
            ->withCount('documents')
            ->get();

        // Attach evaluations to each application
        $applications->each(function ($app) use ($user, $isSecretariat) {
            if ($isSecretariat) {
                // Secretariat sees all evaluations for each application
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
                $app->evaluations = QsEvaluation::where('application_id', $app->id)
                    ->with('evaluator:id,name')
                    ->get();
                $app->evaluation_summary = [
<<<<<<< HEAD
                    'total'        => $app->evaluations->count(),
                    'qualified'    => $app->evaluations->where('overall_qualified', true)->count(),
                    'disqualified' => $app->evaluations->where('overall_qualified', false)->count(),
                ];
=======
                    'total'     => $app->evaluations->count(),
                    'qualified' => $app->evaluations->where('overall_qualified', true)->count(),
                    'disqualified' => $app->evaluations->where('overall_qualified', false)->count(),
                ];
            } else {
                // Members see only their own evaluation
                $app->my_evaluation = QsEvaluation::where('application_id', $app->id)
                    ->where('evaluator_id', $user->id)
                    ->first();
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
            }
        });

        $qsLocked = QsEvaluation::whereIn(
            'application_id',
            Application::where('vacancy_id', $vacancy->id)->pluck('id')
        )->whereNotNull('locked_at')->exists();

        return response()->json([
<<<<<<< HEAD
            'vacancy'        => array_merge(
                $vacancy->only(
                    'id', 'position_title', 'item_number', 'salary_grade',
                    'place_of_assignment', 'status', 'published_at', 'deadline_at'
                ),
                [
                    'education_req'   => $vacancy->education_req,
                    'experience_req'  => $vacancy->experience_req,
                    'training_req'    => $vacancy->training_req,
                    'eligibility_req' => $vacancy->eligibility_req,
                ]
            ),
=======
            'vacancy'        => $vacancy->only('id', 'position_title', 'status', 'deadline_at'),
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
            'applications'   => $applications,
            'is_secretariat' => $isSecretariat,
            'qs_locked'      => $qsLocked,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id'    => 'required|exists:applications,id',
            'education_meets'   => 'required|boolean',
            'experience_meets'  => 'required|boolean',
            'training_meets'    => 'required|boolean',
            'eligibility_meets' => 'required|boolean',
            'remarks'           => 'nullable|string|max:1000',
        ]);

        $application = Application::with('vacancy')->findOrFail($data['application_id']);
        $user = $request->user();

        $composition = $this->getComposition($user->id);
        if (!$composition && !in_array($user->role, ['admin', 'hr-manager'])) {
            return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
        }

<<<<<<< HEAD
        // Members may only evaluate after the Secretariat has submitted their assessment first
        $isSubmitterSecretariat = $this->isSecretariat($user->id)
            || in_array($user->role, ['admin', 'hr-manager']);

        if (!$isSubmitterSecretariat) {
            $secretariatComp = HrmbsboardComposition::where('hrmpsb_role', 'secretariat')
                ->where('is_active', true)
                ->first();

            if ($secretariatComp) {
                $secretariatEvaluated = QsEvaluation::where('application_id', $data['application_id'])
                    ->where('evaluator_id', $secretariatComp->user_id)
                    ->exists();

                if (!$secretariatEvaluated) {
                    return response()->json([
                        'message' => 'The Secretariat must complete their QS evaluation first before members can submit.',
                    ], 422);
                }
            }
        }

=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
        // Prevent re-evaluation if already locked
        $existing = QsEvaluation::where('application_id', $data['application_id'])
            ->where('evaluator_id', $user->id)
            ->first();

        if ($existing?->isLocked()) {
            return response()->json(['message' => 'QS evaluations are locked and cannot be modified.'], 422);
        }

        $overallQualified = $data['education_meets']
            && $data['experience_meets']
            && $data['training_meets']
            && $data['eligibility_meets'];

        $evaluation = QsEvaluation::updateOrCreate(
            ['application_id' => $data['application_id'], 'evaluator_id' => $user->id],
            [
                'education_meets'   => $data['education_meets'],
                'experience_meets'  => $data['experience_meets'],
                'training_meets'    => $data['training_meets'],
                'eligibility_meets' => $data['eligibility_meets'],
                'overall_qualified' => $overallQualified,
                'remarks'           => $data['remarks'] ?? null,
                'evaluated_at'      => now(),
            ]
        );

        AuditLog::record('qs_evaluation_submitted', $application);

        return response()->json($evaluation, 201);
    }

    public function update(Request $request, QsEvaluation $qsEvaluation): JsonResponse
    {
        if ($qsEvaluation->evaluator_id !== $request->user()->id) {
            return response()->json(['message' => 'You can only update your own evaluations.'], 403);
        }

        if ($qsEvaluation->isLocked()) {
            return response()->json(['message' => 'QS evaluations are locked and cannot be modified.'], 422);
        }

        $data = $request->validate([
            'education_meets'   => 'required|boolean',
            'experience_meets'  => 'required|boolean',
            'training_meets'    => 'required|boolean',
            'eligibility_meets' => 'required|boolean',
            'remarks'           => 'nullable|string|max:1000',
        ]);

        $data['overall_qualified'] = $data['education_meets']
            && $data['experience_meets']
            && $data['training_meets']
            && $data['eligibility_meets'];

        $data['evaluated_at'] = now();

        $qsEvaluation->update($data);

        return response()->json($qsEvaluation->fresh());
    }

    public function lock(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (!$this->isSecretariat($user->id) && !in_array($user->role, ['admin', 'hr-manager'])) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can lock QS evaluations.'], 403);
        }

        $applicationIds = Application::where('vacancy_id', $vacancy->id)->pluck('id');

        $count = QsEvaluation::whereIn('application_id', $applicationIds)
            ->whereNull('locked_at')
            ->update(['locked_at' => now()]);

        // Update application statuses based on aggregated QS results
        foreach ($applicationIds as $appId) {
            $evaluations = QsEvaluation::where('application_id', $appId)->get();

            if ($evaluations->isEmpty()) {
                continue;
            }

            // Majority rule: more qualified votes = qualified
            $qualifiedCount = $evaluations->where('overall_qualified', true)->count();
            $totalCount     = $evaluations->count();
            $isQualified    = $qualifiedCount > ($totalCount / 2);

            Application::find($appId)?->update([
                'status' => $isQualified ? 'qualified' : 'disqualified',
            ]);
        }

        // Generate anonymization tokens for all qualifying applications
        (new AnonymizationService())->generateForVacancy($vacancy);

        AuditLog::record('qs_evaluations_locked', $vacancy);

        return response()->json([
            'message'       => 'QS evaluations locked successfully.',
            'locked_count'  => $count,
        ]);
    }
}
