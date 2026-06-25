<?php

namespace App\Exports;

use App\Models\Application;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ApplicantsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $vacancyId;

    public function __construct($vacancyId = null)
    {
        $this->vacancyId = $vacancyId;
    }

    public function collection()
    {
        $query = Application::with([
            'applicant.user',
            'vacancy:id,position_title,place_of_assignment,salary_grade',
        ]);

        if ($this->vacancyId) {
            $query->where('vacancy_id', $this->vacancyId);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Applicant Name',
            'Email',
            'Position Applied',
            'Place of Assignment',
            'Salary Grade',
            'Status',
            'Date Applied',
        ];
    }

    public function map($application): array
    {
        $applicant = $application->applicant;
        $name = $applicant
            ? "{$applicant->last_name}, {$applicant->first_name}"
            : ($application->applicant->user->name ?? 'N/A');

        return [
            $name,
            $application->applicant->user->email ?? 'N/A',
            $application->vacancy->position_title ?? 'N/A',
            $application->vacancy->place_of_assignment ?? 'N/A',
            'SG-' . ($application->vacancy->salary_grade ?? 'N/A'),
            ucfirst(str_replace('_', ' ', $application->status)),
            $application->created_at->format('M d, Y'),
        ];
    }
}
