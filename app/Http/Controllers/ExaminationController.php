<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\Application;
use App\Models\ExamSchedule;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Notifications\ExaminationScheduled;
=======
use App\Models\ExamSchedule;
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
<<<<<<< HEAD
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

    // ── HR Officer CRUD ───────────────────────────────────────────────────

=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
    public function index(): JsonResponse
    {
        return response()->json(ExamSchedule::with('application')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
<<<<<<< HEAD
            'exam_type'      => 'nullable|in:TWE,CBWE',
=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
            'scheduled_at'   => 'required|date|after:now',
            'venue'          => 'required|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $exam = ExamSchedule::create($data);

        return response()->json($exam, 201);
    }

    public function show(ExamSchedule $examination): JsonResponse
    {
        return response()->json($examination->load('application'));
    }

    public function update(Request $request, ExamSchedule $examination): JsonResponse
    {
        $data = $request->validate([
            'scheduled_at' => 'sometimes|required|date',
            'venue'        => 'sometimes|required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $examination->update($data);

        return response()->json($examination);
    }

    public function destroy(ExamSchedule $examination): JsonResponse
    {
        $examination->delete();

        return response()->json(['message' => 'Deleted.']);
    }
<<<<<<< HEAD

    // ── HRMPSB Secretariat Scheduler ─────────────────────────────────────

    public function vacancySchedules(Request $request, Vacancy $vacancy): JsonResponse
    {
        $appIds = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn'])
            ->orderBy('id')
            ->pluck('id');

        $schedules = ExamSchedule::whereIn('application_id', $appIds)
            ->orderBy('scheduled_at')
            ->get();

        $applications = Application::whereIn('id', $appIds)
            ->with('anonymizationToken')
            ->orderBy('id')
            ->get()
            ->map(fn ($app) => [
                'id'     => $app->id,
                'token'  => $app->anonymizationToken?->token ?? ('APP-' . $app->id),
                'status' => $app->status,
            ]);

        return response()->json([
            'vacancy'      => $vacancy->only('id', 'position_title', 'item_number', 'salary_grade', 'place_of_assignment', 'status', 'published_at', 'deadline_at'),
            'can_schedule' => $this->isSecretary($request),
            'applications' => $applications,
            'schedules'    => $schedules,
        ]);
    }

    public function storeHrmpsb(Request $request): JsonResponse
    {
        if (! $this->isSecretary($request)) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can schedule examinations.'], 403);
        }

        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'exam_type'      => 'required|in:TWE,CBWE',
            'scheduled_at'   => 'required|date',
            'venue'          => 'required|string|max:255',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $exam = ExamSchedule::updateOrCreate(
            ['application_id' => $data['application_id'], 'exam_type' => $data['exam_type']],
            ['scheduled_at' => $data['scheduled_at'], 'venue' => $data['venue'], 'notes' => $data['notes'] ?? null]
        );

        return response()->json($exam, 201);
    }

    public function batchSchedule(Request $request, Vacancy $vacancy): JsonResponse
    {
        if (! $this->isSecretary($request)) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can schedule examinations.'], 403);
        }

        $data = $request->validate([
            'exam_type'    => 'required|in:TWE,CBWE',
            'scheduled_at' => 'required|date',
            'venue'        => 'required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $appIds = Application::where('vacancy_id', $vacancy->id)
            ->whereNotIn('status', ['withdrawn'])
            ->pluck('id');

        foreach ($appIds as $appId) {
            ExamSchedule::updateOrCreate(
                ['application_id' => $appId, 'exam_type' => $data['exam_type']],
                ['scheduled_at' => $data['scheduled_at'], 'venue' => $data['venue'], 'notes' => $data['notes'] ?? null]
            );
        }

        return response()->json(['scheduled' => $appIds->count(), 'exam_type' => $data['exam_type']]);
    }

    public function notifyApplicants(Request $request, Vacancy $vacancy): JsonResponse
    {
        if (! $this->isSecretary($request)) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can send notifications.'], 403);
        }

        $data = $request->validate([
            'application_ids'   => 'required|array|min:1',
            'application_ids.*' => 'integer|exists:applications,id',
            'exam_type'         => 'required|in:TWE,CBWE',
        ]);

        $notified = 0;
        $skipped  = 0;

        foreach ($data['application_ids'] as $appId) {
            $schedule = ExamSchedule::where('application_id', $appId)
                ->where('exam_type', $data['exam_type'])
                ->first();

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

            $user->notify(new ExaminationScheduled($schedule, $vacancy->position_title));
            $notified++;
        }

        return response()->json([
            'notified' => $notified,
            'skipped'  => $skipped,
            'exam_type' => $data['exam_type'],
        ]);
    }
=======
>>>>>>> 2ca05292dd7597909b0369c045956779aa52bb03
}
