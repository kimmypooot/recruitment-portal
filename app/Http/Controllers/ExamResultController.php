<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ExamResult;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
<<<<<<< HEAD
    use \App\Traits\FormatsApplicantName;
    private function isActiveMember(int $userId): bool
    {
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
    private function canEncode(int $userId, string $role): bool
    {
        if (in_array($role, ['admin', 'hr-manager'])) {
            return true;
        }
<<<<<<< HEAD
        // All active HRMPSB members encode scores; secretariat consolidates
        return $this->isActiveMember($userId);
=======
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('hrmpsb_role', 'secretariat')
            ->where('is_active', true)
            ->exists();
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
    }

    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (!$this->canEncode($user->id, $user->role)) {
<<<<<<< HEAD
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $canEncode = $this->canEncode($user->id, $user->role);
        $passingThreshold = 70.0;

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->with(['anonymizationToken', 'examResults', 'applicant:id,first_name,last_name,middle_name'])
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->orderBy('id')
            ->get()
            ->map(function ($app) use ($passingThreshold) {
                $token = $app->anonymizationToken;
                $isUnmasked = $token?->isUnmasked();
                $results = $app->examResults->map(fn ($r) => [
                    'id'         => $r->id,
                    'exam_type'  => $r->exam_type,
                    'raw_score'  => $r->raw_score,
                    'max_score'  => $r->max_score,
                    'percentage' => $r->percentage,
                    'passed'     => $r->percentage >= $passingThreshold,
                ]);

                return [
                    'id'           => $app->id,
                    'token'        => $token?->token,
                    'unmasked'     => $isUnmasked,
                    'name'         => $isUnmasked ? $this->formatApplicantName($app->applicant) : null,
                    'status'       => $app->status,
                    'exam_results' => $results,
                    'overall_passed' => $results->isNotEmpty() && $results->every(fn ($r) => $r['passed']),
                    'highest_pct'  => $results->max('percentage'),
                ];
            })
            ->sortByDesc('highest_pct')
            ->values();

        return response()->json([
            'vacancy'           => $vacancy->only(
                'id', 'position_title', 'item_number', 'salary_grade',
                'place_of_assignment', 'status', 'published_at', 'deadline_at'
            ),
            'applications'      => $applications,
            'can_encode'        => $canEncode,
            'passing_threshold' => $passingThreshold,
=======
            $isAssigned = HrmbsboardComposition::where('user_id', $user->id)
                ->where('is_active', true)
                ->exists();

            if (!$isAssigned) {
                return response()->json(['message' => 'Access denied.'], 403);
            }
        }

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->with(['anonymizationToken', 'examResults'])
            ->whereNotIn('status', ['withdrawn'])
            ->get()
            ->map(function ($app) use ($request) {
                $token = $app->anonymizationToken;
                $isUnmasked = $token?->isUnmasked() || in_array($request->user()->role, ['admin', 'hr-manager']);
                return [
                    'id'          => $app->id,
                    'token'       => $token?->token,
                    'unmasked'    => $isUnmasked,
                    'name'        => $isUnmasked ? trim($app->applicant?->first_name . ' ' . $app->applicant?->last_name) : null,
                    'status'      => $app->status,
                    'exam_results' => $app->examResults->map(fn ($r) => [
                        'id'        => $r->id,
                        'exam_type' => $r->exam_type,
                        'raw_score' => $r->raw_score,
                        'max_score' => $r->max_score,
                        'percentage' => $r->percentage,
                    ]),
                ];
            });

        return response()->json([
            'vacancy'      => $vacancy->only('id', 'position_title', 'status'),
            'applications' => $applications,
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'exam_type'      => 'required|in:TWE,CBWE',
<<<<<<< HEAD
            'raw_score'      => 'required|numeric|min:0|max:500',
            'max_score'      => 'required|numeric|min:1|max:500',
=======
            'raw_score'      => 'required|numeric|min:0|max:100',
            'max_score'      => 'required|numeric|min:1|max:100',
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
        ]);

        $application = Application::with('vacancy')->findOrFail($data['application_id']);
        $user = $request->user();

        if (!$this->canEncode($user->id, $user->role)) {
<<<<<<< HEAD
            return response()->json(['message' => 'Only active HRMPSB members can encode exam results.'], 403);
=======
            return response()->json(['message' => 'Only HRMPSB Secretariat can encode exam results.'], 403);
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
        }

        $result = ExamResult::updateOrCreate(
            ['application_id' => $data['application_id'], 'exam_type' => $data['exam_type']],
            [
                'raw_score'  => $data['raw_score'],
                'max_score'  => $data['max_score'],
                'encoded_by' => $user->id,
                'encoded_at' => now(),
            ]
        );

        AuditLog::record('exam_result_encoded', $application);

        return response()->json($result, 201);
    }

    public function destroy(Request $request, ExamResult $examResult): JsonResponse
    {
        $application = Application::with('vacancy')->findOrFail($examResult->application_id);
        $user = $request->user();

        if (!$this->canEncode($user->id, $user->role)) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $examResult->delete();

        AuditLog::record('exam_result_deleted', $application);

        return response()->json(['message' => 'Exam result deleted.']);
    }
}
