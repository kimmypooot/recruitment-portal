<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Where IMIS says a person has signed out of IMIS (docs/sso-logout.md in
 * IMIS). Server-to-server, so there is no session, bearer token or CSRF
 * token: the `sid` IMIS gave at sign-in is the only credential. Revokes the
 * API token that sign-in created, so the SPA's next request gets a 401 and
 * returns to the sign-in page. An unknown or already-revoked `sid` is not
 * an error.
 *
 * Request:  POST sid=<64 characters>
 * Response: 200 {success: true}
 */
class ImisSsoLogoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $sid = $request->input('sid');

        if (is_string($sid) && strlen($sid) === 64) {
            PersonalAccessToken::where('imis_sid_hash', hash('sha256', $sid))->delete();
        }

        return response()->json(['success' => true]);
    }
}
