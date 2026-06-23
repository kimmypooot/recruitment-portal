<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\CsForm;
use App\Services\FormGeneratorService;
use App\Services\PnpkiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsFormController extends Controller
{
    public function index(Application $application): JsonResponse
    {
        $forms = CsForm::where('application_id', $application->id)
            ->with('generatedBy:id,name')
            ->get()
            ->map(fn ($f) => [
                'id'                  => $f->id,
                'form_type'           => $f->form_type,
                'form_label'          => CsForm::TYPES[$f->form_type] ?? $f->form_type,
                'generated_by'        => $f->generatedBy?->name,
                'generated_at'        => $f->generated_at,
                'signed_at'           => $f->signed_at,
                'submitted_to_csc_at' => $f->submitted_to_csc_at,
            ]);

        return response()->json([
            'forms'      => $forms,
            'form_types' => CsForm::TYPES,
            'pnpki_ready'=> (new PnpkiService())->isConfigured(),
        ]);
    }

    public function generate(Request $request, Application $application): JsonResponse
    {
        $data = $request->validate([
            'form_type' => ['required', \Illuminate\Validation\Rule::in(array_keys(CsForm::TYPES))],
        ]);

        $form = (new FormGeneratorService())->generate($application, $data['form_type'], $request->user());

        return response()->json([
            'message'   => CsForm::TYPES[$data['form_type']] . ' generated successfully.',
            'form_type' => $form->form_type,
            'id'        => $form->id,
        ], 201);
    }

    public function download(CsForm $csForm): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($csForm->file_path), 404, 'Form file not found.');

        $filename = "CSForm-{$csForm->form_type}-App{$csForm->application_id}.pdf";

        return Storage::disk('local')->download($csForm->file_path, $filename);
    }

    public function markSubmitted(Request $request, CsForm $csForm): JsonResponse
    {
        (new FormGeneratorService())->markSubmitted($csForm);

        return response()->json(['message' => 'Form marked as submitted to CSC.']);
    }

    public function sign(Request $request, CsForm $csForm): JsonResponse
    {
        $pnpki = new PnpkiService();

        if (!$pnpki->isConfigured()) {
            return response()->json([
                'message' => 'PNPKI is not yet configured. Contact CSC for credentials.',
            ], 422);
        }

        $success = $pnpki->sign($csForm, $request->user()->name);

        return response()->json([
            'message' => $success ? 'Form signed successfully.' : 'Signing failed.',
        ], $success ? 200 : 500);
    }
}
