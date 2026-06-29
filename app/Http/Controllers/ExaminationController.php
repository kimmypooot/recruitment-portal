<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ExamSchedule;
use App\Models\HrmbsboardComposition;
use App\Models\Vacancy;
use App\Notifications\ApplicationStatusUpdated;
use App\Notifications\ExaminationScheduled;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExaminationController extends Controller
{
    private function isSecretary(Request $request): bool
    {
        $user = $request->user();
        if ($user->canAccessAdminModule()) {
            return true;
        }

        return HrmbsboardComposition::where('user_id', $user->id)
            ->whereIn('hrmpsb_role', ['secretariat', 'hr-chief'])
            ->where('is_active', true)
            ->exists();
    }

    // ── HR Officer CRUD ───────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        return response()->json(ExamSchedule::with('application')->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'exam_type' => 'nullable|in:TWE,CBWE',
            'scheduled_at' => 'required|date|after:now',
            'venue' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
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
            'venue' => 'sometimes|required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $examination->update($data);

        return response()->json($examination);
    }

    public function destroy(ExamSchedule $examination): JsonResponse
    {
        $examination->delete();

        return response()->json(['message' => 'Deleted.']);
    }

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
                'id' => $app->id,
                'token' => $app->anonymizationToken?->token ?? ('APP-'.$app->id),
                'status' => $app->status,
            ]);

        return response()->json([
            'vacancy' => $vacancy->only('id', 'position_title', 'plantilla_no', 'salary_grade', 'place_of_assignment', 'status', 'published_at', 'deadline_at'),
            'can_schedule' => $this->isSecretary($request),
            'applications' => $applications,
            'schedules' => $schedules,
        ]);
    }

    public function storeHrmpsb(Request $request): JsonResponse
    {
        if (! $this->isSecretary($request)) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can schedule examinations.'], 403);
        }

        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'exam_type' => 'required|in:TWE,CBWE',
            'scheduled_at' => 'required|date',
            'venue' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $exam = ExamSchedule::updateOrCreate(
            ['application_id' => $data['application_id'], 'exam_type' => $data['exam_type']],
            ['scheduled_at' => $data['scheduled_at'], 'venue' => $data['venue'], 'notes' => $data['notes'] ?? null]
        );

        $application = Application::find($data['application_id']);
        if ($application && $application->status === 'qualified') {
            $oldStatus = $application->status;
            $application->update(['status' => 'exam_scheduled', 'reviewed_at' => now()]);
            if ($user = $application->applicant?->user) {
                $user->notify(new ApplicationStatusUpdated($application, $oldStatus, 'exam_scheduled', silent: true));
            }
        }

        return response()->json($exam, 201);
    }

    public function batchSchedule(Request $request, Vacancy $vacancy): JsonResponse
    {
        if (! $this->isSecretary($request)) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can schedule examinations.'], 403);
        }

        $data = $request->validate([
            'exam_type' => 'required|in:TWE,CBWE',
            'scheduled_at' => 'required|date',
            'venue' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
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

        Application::whereIn('id', $appIds)
            ->where('status', 'qualified')
            ->update(['status' => 'exam_scheduled', 'reviewed_at' => now()]);

        return response()->json(['scheduled' => $appIds->count(), 'exam_type' => $data['exam_type']]);
    }

    public function notifyApplicants(Request $request, Vacancy $vacancy): JsonResponse
    {
        if (! $this->isSecretary($request)) {
            return response()->json(['message' => 'Only the HRMPSB Secretariat can send notifications.'], 403);
        }

        $data = $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'integer|exists:applications,id',
            'exam_type' => 'required|in:TWE,CBWE',
        ]);

        $notified = 0;
        $skipped = 0;

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
            'skipped' => $skipped,
            'exam_type' => $data['exam_type'],
        ]);
    }
}
