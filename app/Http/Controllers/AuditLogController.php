<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = DB::table('audit_logs')->latest()->paginate(50);
        return response()->json($logs);
    }
}
