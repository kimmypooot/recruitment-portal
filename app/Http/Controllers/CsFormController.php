<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\CsForm;
use App\Models\HrmpsbComposition;
use App\Services\FormGeneratorService;
use App\Services\PnpkiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsFormController extends Controller
{
    /**
     * These routes previously relied on nothing but the blanket
     * role:hrmpsb,admin route middleware — unlike QS/CBWE/BEI, which also
     * require an active HrmpsbComposition row (HRMPSB composition is global,
     * not per-vacancy, so that's the only membership check those stages
     * apply either; there's no narrower "assigned to this vacancy" concept
     * to match). That gap let an hrmpsb-role account with no board seat at
     * all — never assigned, or since deactivated — generate, sign, or mark
     * CS Forms as submitted to CSC.
     */
    private function isActiveHrmpsbMember(int $userId): bool
    {
        return HrmpsbComposition::where('user_id', $userId)
            ->where('is_active', true)
            ->exists();
    }

    public function index(Request $request, Application $application): JsonResponse
    {
        $user = $request->user();
        if (! $user->canAccessAdminModule() && ! $this->isActiveHrmpsbMember($user->id)) {
            return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
        }

        $forms = CsForm::where('application_id', $application->id)
            ->with('generatedBy:id,first_name,last_name,middle_name,suffix')
            ->get()
            ->map(fn ($f) => [
                'id'                  => $f->id,
                'form_type'           => $f->form_type,
                'form_label'          => CsForm::TYPES[$f->form_type] ?? $f->form_type,
                'generated_by'        => $f->generatedBy?->full_name,
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
        $user = $request->user();
        if (! $user->canAccessAdminModule() && ! $this->isActiveHrmpsbMember($user->id)) {
            return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
        }

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

    public function download(Request $request, CsForm $csForm): StreamedResponse|JsonResponse
    {
        $user = $request->user();
        if (! $user->canAccessAdminModule() && ! $this->isActiveHrmpsbMember($user->id)) {
            return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
        }

        abort_unless(Storage::disk('local')->exists($csForm->file_path), 404, 'Form file not found.');

        $filename = "CSForm-{$csForm->form_type}-App{$csForm->application_id}.pdf";

        return Storage::disk('local')->download($csForm->file_path, $filename);
    }

    public function markSubmitted(Request $request, CsForm $csForm): JsonResponse
    {
        $user = $request->user();
        if (! $user->canAccessAdminModule() && ! $this->isActiveHrmpsbMember($user->id)) {
            return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
        }

        (new FormGeneratorService())->markSubmitted($csForm);

        return response()->json(['message' => 'Form marked as submitted to CSC.']);
    }

    public function sign(Request $request, CsForm $csForm): JsonResponse
    {
        $user = $request->user();
        if (! $user->canAccessAdminModule() && ! $this->isActiveHrmpsbMember($user->id)) {
            return response()->json(['message' => 'You are not an active HRMPSB member.'], 403);
        }

        $pnpki = new PnpkiService();

        if (!$pnpki->isConfigured()) {
            return response()->json([
                'message' => 'PNPKI is not yet configured. Contact CSC for credentials.',
            ], 422);
        }

        try {
            $pnpki->sign($csForm, $request->user()->full_name);
        } catch (\RuntimeException $e) {
            // Signing is deliberately unimplemented; surface why rather than
            // reporting a success the system cannot actually deliver.
            return response()->json(['message' => $e->getMessage()], 501);
        }

        return response()->json(['message' => 'Form signed successfully.']);
    }
}
