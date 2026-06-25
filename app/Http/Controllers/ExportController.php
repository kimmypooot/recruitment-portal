<?php

namespace App\Http\Controllers;

use App\Exports\ApplicantsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function applicants(?int $vacancyId = null): BinaryFileResponse
    {
        $filename = 'applicants-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new ApplicantsExport($vacancyId), $filename);
    }

    public function auditLogs(): BinaryFileResponse
    {
        $filename = 'audit-logs-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new \App\Exports\AuditLogsExport(), $filename);
    }
}
