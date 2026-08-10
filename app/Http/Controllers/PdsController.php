<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ApplicantProfile;
use App\Models\Application;
use App\Services\AuditLog;
use App\Services\Pds\PdsCompletenessChecker;
use App\Services\Pds\PdsExcelGenerator;
use App\Services\Pds\PdsPdfGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates CS Form No. 212 (Revised 2026) from an applicant's profile.
 *
 * Kept separate from CsFormController: that one is Application-scoped and writes
 * CsForm rows, whereas the PDS belongs to the ApplicantProfile and exists
 * independently of any application.
 */
class PdsController extends Controller
{
    private const FORMATS = ['xlsx', 'pdf'];

    public function __construct(
        private readonly PdsCompletenessChecker $checker,
        private readonly PdsExcelGenerator $excelGenerator,
        private readonly PdsPdfGenerator $pdfGenerator,
    ) {}

    /** Per-section completeness for the signed-in applicant. */
    public function completeness(Request $request): JsonResponse
    {
        $profile = $this->ownProfile($request);

        return response()->json($this->checker->check($profile));
    }

    /** The signed-in applicant's own PDS. */
    public function download(Request $request): BinaryFileResponse|JsonResponse
    {
        $profile = $this->ownProfile($request);

        return $this->stream($request, $profile, 'pds_generated');
    }

    /**
     * A candidate's PDS, for HRMPSB board members and the admin module.
     */
    public function downloadForApplication(Request $request, Application $application): BinaryFileResponse|JsonResponse
    {
        $this->authorize('viewForApplication', [ApplicantProfile::class, $application]);

        $profile = $application->applicant;

        if ($profile === null) {
            abort(Response::HTTP_NOT_FOUND, 'This application has no applicant profile.');
        }

        return $this->stream($request, $profile, 'pds_viewed_by_board', $application);
    }

    // --- Internals ----------------------------------------------------------

    private function ownProfile(Request $request): ApplicantProfile
    {
        // Queried rather than read off the relation accessor: the authenticated
        // User caches `applicantProfile` on first access, and a cached copy can
        // predate writes made earlier in the same lifecycle — which would make
        // completeness and generation act on stale data.
        $profile = $request->user()->applicantProfile()->first();

        if ($profile === null) {
            abort(Response::HTTP_NOT_FOUND, 'You do not have an applicant profile yet.');
        }

        return $profile;
    }

    private function stream(
        Request $request,
        ApplicantProfile $profile,
        string $auditAction,
        ?Application $application = null,
    ): BinaryFileResponse|JsonResponse {
        $format = strtolower((string) $request->query('format', 'xlsx'));

        if (! in_array($format, self::FORMATS, true)) {
            return response()->json([
                'message' => 'Unsupported format. Use xlsx or pdf.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $check = $this->checker->check($profile);

        if (! $check['can_generate']) {
            return response()->json([
                'message'  => 'The Personal Data Sheet is not complete enough to generate.',
                'blocking' => $check['blocking'],
                'sections' => $check['sections'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $path = storage_path('app/pds-tmp/'.$this->fileName($profile, $format));

        try {
            $format === 'pdf'
                ? $this->pdfGenerator->generate($profile, $path)
                : $this->excelGenerator->generate($profile, $path);
        } catch (RuntimeException $e) {
            // Most likely LibreOffice missing on this host; the xlsx path still works.
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $profile->forceFill(['pds_generated_at' => now()])->save();

        AuditLog::record($auditAction, $application ?? $profile);

        return response()->download($path, $this->fileName($profile, $format))->deleteFileAfterSend();
    }

    private function fileName(ApplicantProfile $profile, string $format): string
    {
        $name = trim(($profile->last_name ?? 'applicant').'_'.($profile->first_name ?? (string) $profile->id));

        return 'PDS_'.preg_replace('/[^A-Za-z0-9_]+/', '_', $name).'.'.$format;
    }
}
