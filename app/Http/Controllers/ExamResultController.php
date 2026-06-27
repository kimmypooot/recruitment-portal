<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ExamResult;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Services\AuditLog;
use App\Traits\FormatsApplicantName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    use FormatsApplicantName;

    private function isActiveMember(int $userId): bool
    {
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    private function canEncode(int $userId, \App\Models\User $user): bool
    {
        if ($user->canAccessAdminModule()) {
            return true;
        }

        return $this->isActiveMember($userId);
    }

    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (! $this->canEncode($user->id, $user)) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $canEncode = $this->canEncode($user->id, $user);
        $passingThreshold = 70.0;

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->with(['anonymizationToken', 'examResults', 'applicant:id,user_id', 'applicant.user:id,first_name,last_name,middle_name,suffix'])
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->orderBy('id')
            ->get()
            ->map(function ($app) use ($passingThreshold) {
                $token = $app->anonymizationToken;
                $isUnmasked = $token?->isUnmasked();
                $results = $app->examResults->map(fn ($r) => [
                    'id' => $r->id,
                    'exam_type' => $r->exam_type,
                    'raw_score' => $r->raw_score,
                    'max_score' => $r->max_score,
                    'percentage' => $r->percentage,
                    'passed' => $r->percentage >= $passingThreshold,
                ]);

                return [
                    'id' => $app->id,
                    'token' => $token?->token,
                    'unmasked' => $isUnmasked,
                    'name' => $isUnmasked ? $this->formatApplicantName($app->applicant) : null,
                    'status' => $app->status,
                    'exam_results' => $results,
                    'overall_passed' => $results->isNotEmpty() && $results->every(fn ($r) => $r['passed']),
                    'highest_pct' => $results->max('percentage'),
                ];
            })
            ->sortByDesc('highest_pct')
            ->values();

        return response()->json([
            'vacancy' => $vacancy->only(
                'id', 'position_title', 'plantilla_no', 'salary_grade',
                'place_of_assignment', 'status', 'published_at', 'deadline_at'
            ),
            'applications' => $applications,
            'can_encode' => $canEncode,
            'passing_threshold' => $passingThreshold,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'exam_type' => 'required|in:TWE,CBWE',
            'raw_score' => 'required|numeric|min:0|max:100|lte:max_score',
            'max_score' => 'required|numeric|min:1|max:100',
        ]);

        $application = Application::with('vacancy')->findOrFail($data['application_id']);
        $user = $request->user();

        if (! $this->canEncode($user->id, $user)) {
            return response()->json(['message' => 'Only active HRMPSB members can encode exam results.'], 403);
        }

        $result = ExamResult::updateOrCreate(
            ['application_id' => $data['application_id'], 'exam_type' => $data['exam_type']],
            [
                'raw_score' => $data['raw_score'],
                'max_score' => $data['max_score'],
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

        if (! $this->canEncode($user->id, $user)) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $examResult->delete();

        AuditLog::record('exam_result_deleted', $application);

        return response()->json(['message' => 'Exam result deleted.']);
    }
}
