<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ExamResult;
use App\Models\HrmbsboardComposition;
use App\Models\InterviewSchedule;
use App\Models\Vacancy;
use App\Notifications\InterviewScheduled;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    private const PASSING_THRESHOLD = 70.0;

    private function isSecretary(Request $request): bool
    {
        $user = $request->user();
        if (in_array($user->role, ['admin', 'hr-manager'])) {
            return true;
        }
        return HrmbsboardComposition::where('user_id', $user->id)
            ->where('hrmpsb_role', 'secretariat')
            ->where('is_active', true)
            ->exists();
    }

    private function examPasserIds($appIds): \Illuminate\Support\Collection
    {
        $results = ExamResult::whereIn('application_id', $appIds)->get()->groupBy('application_id');

        return $appIds->filter(function ($id) use ($results) {
            $group = $results->get($id, collect());
            return $group->isNotEmpty() && $group->every(fn ($r) => $r->percentage >= self::PASSING_THRESHOLD);
        })->values();
    }

    // ── HR Officer CRUD ───────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        return response()->json(InterviewSchedule::with('application')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'scheduled_at'   => 'required|date|after:now',
            'venue'          => 'required|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $interview = InterviewSchedule::create($data);

        return response()->json($interview, 201);
    }

    public function show(InterviewSchedule $interview): JsonResponse
    {
        return response()->json($interview->load('application'));
    }

    public function update(Request $request, InterviewSchedule $interview): JsonResponse
    {
        $data = $request->validate([
            'scheduled_at' => 'sometimes|required|date',
            'venue'        => 'sometimes|required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $interview->update($data);

        return response()->json($interview);
    }

    public function destroy(InterviewSchedule $interview): JsonResponse
    {
        $interview->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    // ── HRMPSB Secretariat BEI Scheduler ─────────────────────────────────

    public function vacancySchedules(Request $request, Vacancy $vacancy): JsonResponse
    {
        $appIds = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->orderBy('id')
            ->pluck('id');

        $passerIds = $this->examPasserIds($appIds);

        $schedules = InterviewSchedule::whereIn('application_id', $passerIds)
            ->orderBy('scheduled_at')
            ->get();

        $applications = Application::whereIn('id', $passerIds)
            ->with(['anonymizationToken', 'examResults'])
            ->orderBy('id')
            ->get()
            ->map(fn ($app) => [
                'id'           => $app->id,
                'token'        => $app->anonymizationToken?->token ?? ('APP-' . $app->id),
                'status'       => $app->status,
                'exam_results' => $app->examResults->map(fn ($r) => [
                    'exam_type'  => $r->exam_type,
                    'percentage' => $r->percentage,
                    'passed'     => $r->percentage >= self::PASSING_THRESHOLD,
                ]),
            ]);

        return response()->json([
            'vacancy'           => $vacancy->only('id', 'position_title', 'item_number', 'salary_grade', 'place_of_assignment', 'status', 'published_at', 'deadline_at'),
            'can_schedule'      => $this->isSecretary($request),
            'applications'      => $applications,
            'schedules'         => $schedules,
            'passing_threshold' => self::PASSING_THRESHOLD,
        ]);
    }

    public function storeHrmpsb(Request $request): JsonResponse
    {
        if (! $this->isSecretary($request)) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can schedule BEI.'], 403);
        }

        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'scheduled_at'   => 'required|date',
            'venue'          => 'required|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $interview = InterviewSchedule::updateOrCreate(
            ['application_id' => $data['application_id']],
            ['scheduled_at' => $data['scheduled_at'], 'venue' => $data['venue'], 'notes' => $data['notes'] ?? null]
        );

        return response()->json($interview, 201);
    }

    public function batchSchedule(Request $request, Vacancy $vacancy): JsonResponse
    {
        if (! $this->isSecretary($request)) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can schedule BEI.'], 403);
        }

        $data = $request->validate([
            'scheduled_at' => 'required|date',
            'venue'        => 'required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $appIds = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn', 'disqualified'])
            ->pluck('id');

        $passerIds = $this->examPasserIds($appIds);

        foreach ($passerIds as $appId) {
            InterviewSchedule::updateOrCreate(
                ['application_id' => $appId],
                ['scheduled_at' => $data['scheduled_at'], 'venue' => $data['venue'], 'notes' => $data['notes'] ?? null]
            );
        }

        return response()->json(['scheduled' => $passerIds->count()]);
    }

    public function notifyApplicants(Request $request, Vacancy $vacancy): JsonResponse
    {
        if (! $this->isSecretary($request)) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can send notifications.'], 403);
        }

        $data = $request->validate([
            'application_ids'   => 'required|array|min:1',
            'application_ids.*' => 'integer|exists:applications,id',
        ]);

        $notified = 0;
        $skipped  = 0;

        foreach ($data['application_ids'] as $appId) {
            $schedule = InterviewSchedule::where('application_id', $appId)->first();

            if (! $schedule) {
                $skipped++;
                continue;
            }

            $user = Application::with('applicant.user')
                ->find($appId)
                ?->applicant
                ?->user;

            if (! $user) {
                $skipped++;
                continue;
            }

            $user->notify(new InterviewScheduled($schedule, $vacancy->position_title));
            $notified++;
        }

        return response()->json([
            'notified' => $notified,
            'skipped'  => $skipped,
        ]);
    }
}
