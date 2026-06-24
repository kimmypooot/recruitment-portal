<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;

                if (!isset($data['message'])) {
                    $data['message'] = match ($data['type'] ?? '') {
                        'exam_scheduled' => 'You have been scheduled for an examination.',
                        'bei_scheduled'  => 'You have been scheduled for a Behavioral Event Interview.',
                        default          => 'You have a new notification.',
                    };
                }

                return [
                    'id'         => $notification->id,
                    'data'       => $data,
                    'read_at'    => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'ok']);
    }
}
