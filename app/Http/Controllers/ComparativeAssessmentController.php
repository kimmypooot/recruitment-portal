<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ComparativeAssessmentResult;
use App\Models\Vacancy;
use App\Services\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComparativeAssessmentController extends Controller
{
    public function index(Vacancy $vacancy): JsonResponse
    {
        $car = ComparativeAssessmentResult::where('vacancy_id', $vacancy->id)->latest('generated_at')->first();

        return response()->json([
            'exists' => $car !== null,
            'generated_at' => $car?->generated_at,
            'download_url' => $car ? url("/api/deliberation/{$vacancy->id}/comparative-assessment/download") : null,
        ]);
    }

    public function generate(Request $request, Vacancy $vacancy): JsonResponse
    {
        $user = $request->user();

        $canGenerate = $user->canAccessAdminModule()
            || \App\Models\HrmbsboardComposition::where('user_id', $user->id)
                ->whereIn('hrmpsb_role', ['chairperson', 'secretariat'])
                ->where('is_active', true)
                ->exists();

        if (! $canGenerate) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $applications = Application::with([
            'examScores',
            'backgroundInvestigationReports',
            'eoptResults',
            'deliberationResult',
        ])->where('vacancy_id', $vacancy->id)
          ->whereIn('status', ['recommended', 'appointed', 'failed', 'disqualified'])
          ->get();

        $pdf = Pdf::loadView('forms.comparative-assessment', [
            'vacancy'      => $vacancy,
            'applications' => $applications,
            'generatedAt'  => now(),
        ])->setPaper('legal', 'landscape');

        $filename = 'comparative-assessment-' . $vacancy->id . '-' . now()->format('Ymd-His') . '.pdf';
        $filePath = "comparative-assessments/{$vacancy->id}/{$filename}";

        Storage::disk('local')->put($filePath, $pdf->output());

        $car = ComparativeAssessmentResult::updateOrCreate(
            ['vacancy_id' => $vacancy->id],
            [
                'file_path'    => $filePath,
                'generated_by' => $user->id,
                'generated_at' => now(),
            ]
        );

        AuditLog::record('comparative_assessment_generated', $vacancy);

        return response()->json([
            'message'      => 'Comparative Assessment Result generated successfully.',
            'generated_at' => $car->generated_at,
            'download_url' => url("/api/deliberation/{$vacancy->id}/comparative-assessment/download"),
        ], 201);
    }

    public function download(Vacancy $vacancy)
    {
        $car = ComparativeAssessmentResult::where('vacancy_id', $vacancy->id)->latest('generated_at')->firstOrFail();

        if (! Storage::disk('local')->exists($car->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download($car->file_path, "Comparative-Assessment-{$vacancy->position_title}.pdf");
    }
}
