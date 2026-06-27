<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function testimonials(): JsonResponse
    {
        $items = ApplicationFeedback::with([
            'applicant.user:id,first_name,last_name',
            'vacancy:id,position_title',
        ])
        ->where('rating', '>=', 4)
        ->whereNotNull('comment')
        ->where('comment', '!=', '')
        ->latest()
        ->limit(20)
        ->get()
        ->map(fn ($fb) => [
            'rating'         => $fb->rating,
            'comment'        => $fb->comment,
            'position_title' => $fb->vacancy?->position_title ?? '—',
            'display_name'   => $this->anonymizeName($fb->applicant?->user),
        ]);

        return response()->json($items);
    }

    private function anonymizeName($user): string
    {
        if (!$user) return 'Anonymous';
        $first = $user->first_name ?? '';
        $last  = $user->last_name  ?? '';
        return trim($first . ' ' . ($last ? strtoupper($last[0]) . '.' : '')) ?: 'Anonymous';
    }

    public function store(Request $request, Application $application): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'applicant') {
            return response()->json(['message' => 'Only applicants may submit feedback.'], 403);
        }

        $profile = $user->applicantProfile;

        if (!$profile || $application->applicant_id !== $profile->id) {
            return response()->json(['message' => 'You may only submit feedback for your own applications.'], 403);
        }

        if (ApplicationFeedback::where('application_id', $application->id)->exists()) {
            return response()->json(['message' => 'Feedback has already been submitted for this application.'], 422);
        }

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $feedback = ApplicationFeedback::create([
            'application_id' => $application->id,
            'applicant_id'   => $profile->id,
            'vacancy_id'     => $application->vacancy_id,
            'rating'         => $data['rating'],
            'comment'        => $data['comment'] ?? null,
        ]);

        return response()->json($feedback, 201);
    }

    public function index(Request $request): JsonResponse
    {
        $query = ApplicationFeedback::with([
            'applicant.user:id,first_name,last_name,middle_name',
            'vacancy:id,position_title',
        ]);

        if ($request->filled('rating')) {
            $query->where('rating', $request->integer('rating'));
        }

        if ($request->filled('vacancy_id')) {
            $query->where('vacancy_id', $request->integer('vacancy_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->whereHas('applicant.user', function ($q) use ($search) {
                $q->where('first_name', 'like', $search)
                  ->orWhere('last_name', 'like', $search);
            })->orWhereHas('vacancy', function ($q) use ($search) {
                $q->where('position_title', 'like', $search);
            });
        }

        $total   = (clone $query)->count();
        $average = $total > 0 ? round((clone $query)->avg('rating'), 1) : null;

        $ratingCounts = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingCounts[$i] = (clone $query)->where('rating', $i)->count();
        }

        $feedbacks = $query->latest()->paginate(20);

        return response()->json([
            'feedbacks'     => $feedbacks,
            'stats' => [
                'total'         => $total,
                'average'       => $average,
                'rating_counts' => $ratingCounts,
            ],
        ]);
    }
}
