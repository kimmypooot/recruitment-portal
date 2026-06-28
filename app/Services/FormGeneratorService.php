<?php

namespace App\Services;

use App\Models\Application;
use App\Models\CsForm;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class FormGeneratorService
{
    public function generate(Application $application, string $formType, User $generatedBy): CsForm
    {
        $application->load(['applicant.user', 'applicant.workExperiences', 'applicant.educationalAttainments', 'applicant.trainings', 'vacancy']);

        $view = match ($formType) {
            '33A'   => 'forms.cs-form-33a',
            '33B'   => 'forms.cs-form-33b',
            'form1' => 'forms.cs-form-1-2025',
            default => throw new \InvalidArgumentException("Unknown form type: {$formType}"),
        };

        $pdf = Pdf::loadView($view, [
            'application' => $application,
            'profile'     => $application->applicant,
            'vacancy'     => $application->vacancy,
            'generatedAt' => now(),
        ])->setPaper('legal', 'portrait');

        $path = "cs-forms/{$application->id}/{$formType}-" . now()->format('Ymd-His') . '.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        $form = CsForm::updateOrCreate(
            ['application_id' => $application->id, 'form_type' => $formType],
            [
                'file_path'    => $path,
                'generated_by' => $generatedBy->id,
                'generated_at' => now(),
                'signed_at'    => null,
            ]
        );

        AuditLog::record("cs_form_generated:{$formType}", $application);

        return $form;
    }

    public function markSubmitted(CsForm $form): void
    {
        $form->update(['submitted_to_csc_at' => now()]);

        AuditLog::record('cs_form_submitted_to_csc', $form->application);
    }
}
