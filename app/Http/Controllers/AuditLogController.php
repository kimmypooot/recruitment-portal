<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DB::table('audit_logs as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select(
                'a.id', 'a.action', 'a.auditable_type', 'a.auditable_id',
                'a.user_id', 'u.name as user_name', 'a.created_at'
            )
            ->latest('a.created_at');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('a.action', 'like', "%{$q}%")
                    ->orWhere('a.auditable_type', 'like', "%{$q}%")
                    ->orWhere('u.name', 'like', "%{$q}%");
            });
        }

        if ($request->filled('action_type')) {
            $query->where('a.action', 'like', $request->action_type . '%');
        }

        $logs = $query->paginate(50);

        return response()->json($logs);
    }
}
