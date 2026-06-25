<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\EoptResult;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Services\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EoptController extends Controller
{
    private function isSecretariat(int $userId): bool
    {
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('hrmpsb_role', 'secretariat')
            ->where('is_active', true)
            ->exists();
    }

    private function isMemberOrAbove(int $userId): bool
    {
        return HrmbsboardComposition::where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    public function index(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $isSecretariat = $this->isSecretariat($user->id)
            || in_array($user->role, ['admin', 'hr-manager', 'hrmpsb-secretariat']);

        $isMember = $isSecretariat || $this->isMemberOrAbove($user->id)
            || in_array($user->role, ['admin', 'hr-manager', 'appointing-authority']);

        if (! $isMember) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $applications = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->with(['anonymizationToken', 'applicant', 'eoptResult'])
            ->get()
            ->map(function ($app) use ($isSecretariat) {
                $token = $app->anonymizationToken;
                $eopt = $app->eoptResult;

                return [
                    'id' => $app->id,
                    'token' => $token?->token,
                    'name' => $isSecretariat ? ($app->applicant?->first_name.' '.$app->applicant?->last_name) : null,
                    'status' => $app->status,
                    'eopt' => $eopt ? [
                        'emotional_stability' => $eopt->emotional_stability,
                        'extraversion' => $eopt->extraversion,
                        'openness_to_experience' => $eopt->openness_to_experience,
                        'agreeableness' => $eopt->agreeableness,
                        'conscientiousness' => $eopt->conscientiousness,
                        'remarks' => $eopt->remarks,
                    ] : null,
                ];
            });

        return response()->json([
            'vacancy' => $vacancy->only('id', 'position_title', 'item_number', 'salary_grade', 'place_of_assignment', 'status'),
            'applications' => $applications,
            'is_secretariat' => $isSecretariat,
            'definitions' => EoptResult::getAllDefinitions(),
            'rating_labels' => array_combine(EoptResult::RATINGS, array_map(fn ($r) => EoptResult::ratingLabel($r), EoptResult::RATINGS)),
            'categories' => EoptResult::CATEGORIES,
        ]);
    }

    public function store(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        if (! $this->isSecretariat($user->id) && ! in_array($user->role, ['admin', 'hr-manager', 'hrmpsb-secretariat'])) {
            return response()->json(['message' => 'Only the secretariat can submit EOPT ratings.'], 403);
        }

        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'emotional_stability' => 'required|in:very_high,high,average,low,very_low',
            'extraversion' => 'required|in:very_high,high,average,low,very_low',
            'openness_to_experience' => 'required|in:very_high,high,average,low,very_low',
            'agreeableness' => 'required|in:very_high,high,average,low,very_low',
            'conscientiousness' => 'required|in:very_high,high,average,low,very_low',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $application = Application::findOrFail($data['application_id']);

        $result = EoptResult::updateOrCreate(
            ['application_id' => $data['application_id']],
            [
                'emotional_stability' => $data['emotional_stability'],
                'extraversion' => $data['extraversion'],
                'openness_to_experience' => $data['openness_to_experience'],
                'agreeableness' => $data['agreeableness'],
                'conscientiousness' => $data['conscientiousness'],
                'remarks' => $data['remarks'] ?? null,
            ]
        );

        AuditLog::record('eopt_rated', $application);

        return response()->json($result, 201);
    }
}
