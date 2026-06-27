<?php

namespace App\Exports;

use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditLogsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return AuditLog::with('user')->latest()->get();
    }

    public function headings(): array
    {
        return [
            'User',
            'Action',
            'Model',
            'Details',
            'Date',
        ];
    }

    public function map($log): array
    {
        return [
            $log->user?->full_name ?? 'System',
            $log->action,
            $log->model_type,
            $log->details ?? '',
            $log->created_at->format('M d, Y h:i A'),
        ];
    }
}
