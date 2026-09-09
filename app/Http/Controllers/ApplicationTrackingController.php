<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public, unauthenticated "Track My Application" lookup
 * (docs/strategic-feature-scoping.md). Deliberately minimal: a reference
 * code alone must never resolve to anything — the submitted email has to
 * match the account on file too — and the response carries only status,
 * timestamps, and the vacancy title. No documents, no other personal data.
 */
class ApplicationTrackingController extends Controller
{
    public function track(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reference_code' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        $code = strtoupper(trim($data['reference_code']));

        $application = Application::where('reference_code', $code)
            ->with(['vacancy:id,position_title,place_of_assignment,status'])
            ->first();

        $emailMatches = $application
            && $application->applicant?->user
            && strcasecmp($application->applicant->user->email, $data['email']) === 0;

        // Same generic message whether the code doesn't exist or the email
        // doesn't match it — telling the two apart would let someone confirm
        // a guessed code belongs to a real application.
        if (! $emailMatches) {
            return response()->json([
                'message' => 'No application found for that reference code and email. Please check both and try again.',
            ], 404);
        }

        return response()->json([
            'reference_code' => $application->reference_code,
            'status' => $application->status,
            'submitted_at' => $application->submitted_at,
            'last_updated_at' => $application->updated_at,
            'vacancy' => [
                'position_title' => $application->vacancy?->position_title,
                'place_of_assignment' => $application->vacancy?->place_of_assignment,
            ],
        ]);
    }
}
