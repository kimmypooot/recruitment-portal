<?php

namespace App\Http\Controllers;

use App\Models\ApplicantProfile;
use App\Models\User;
use App\Models\WorkExperience;
use App\Models\EducationalAttainment;
use App\Models\Training;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $user    = Auth::user();
        $profile = ApplicantProfile::with(['workExperiences', 'educationalAttainments', 'trainings'])
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'user'        => $user,
            'profile'     => $profile,
            'is_complete' => $profile?->isComplete() ?? false,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'        => 'sometimes|required|string|max:100',
            'last_name'         => 'sometimes|required|string|max:100',
            'middle_name'       => 'nullable|string|max:100',
            'suffix'            => 'nullable|string|max:20',
            'gender'            => 'nullable|string|max:20',
            'civil_status'      => 'nullable|string|max:30',
            'birthday'          => 'nullable|date',
            'religion'          => 'nullable|string|max:100',
            'region'            => 'nullable|string|max:100',
            'province'          => 'nullable|string|max:100',
            'city_municipality' => 'nullable|string|max:100',
            'barangay'          => 'nullable|string|max:100',
            'mobile_number'     => 'nullable|string|max:20',
            'eligibility'       => 'nullable|string|max:150',
            'eligibility_other' => 'nullable|string|max:200',
            'indigenous_group'  => 'nullable|string|in:Yes,No',
            'pwd'               => 'nullable|string|in:Yes,No',
            'solo_parent'       => 'nullable|string|in:Yes,No',
        ]);

        $nameKeys  = ['first_name', 'last_name', 'middle_name', 'suffix'];
        $nameData  = array_intersect_key($data, array_flip($nameKeys));
        $profileData = array_diff_key($data, array_flip($nameKeys));

        $user = Auth::user();

        if (! empty($nameData)) {
            $firstName  = trim($nameData['first_name'] ?? $user->first_name);
            $lastName   = trim($nameData['last_name'] ?? $user->last_name);
            $middleName = array_key_exists('middle_name', $nameData) ? $nameData['middle_name'] : $user->middle_name;
            $suffix     = array_key_exists('suffix', $nameData) ? $nameData['suffix'] : $user->suffix;
            $middleName = $middleName ? trim($middleName) : null;
            $suffix     = $suffix ? trim($suffix) : null;

            DB::transaction(function () use ($user, $firstName, $lastName, $middleName, $suffix) {
                $duplicate = User::lockForUpdate()
                    ->where('id', '!=', $user->id)
                    ->whereRaw('LOWER(first_name) = ? AND LOWER(last_name) = ?', [
                        strtolower($firstName),
                        strtolower($lastName),
                    ])
                    ->when($middleName, fn ($q, $m) => $q->whereRaw('LOWER(middle_name) = ?', [strtolower($m)]))
                    ->first();

                if ($duplicate) {
                    abort(409, 'Another account already uses this name. Please contact support if this is an error.');
                }

                $user->update([
                    'first_name'  => $firstName,
                    'last_name'   => $lastName,
                    'middle_name' => $middleName,
                    'suffix'      => $suffix,
                ]);
            });
        }

        $profile = $this->getOrCreateProfile();
        $profile->fill($profileData);

        if ($profile->isComplete() && $profile->profile_completed_at === null) {
            $profile->profile_completed_at = now();
        }

        $profile->save();

        return response()->json([
            'user'        => $user->fresh(),
            'profile'     => $profile->fresh(['workExperiences', 'educationalAttainments', 'trainings']),
            'is_complete' => $profile->isComplete(),
        ]);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|file|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:3072',
        ]);

        $profile = $this->getOrCreateProfile();
        $userId  = Auth::id();

        if ($profile->photo_path) {
            Storage::disk('public')->delete($profile->photo_path);
        }

        $profile->photo_path = $request->file('photo')->store("profile-photos/{$userId}", 'public');
        $profile->save();

        return response()->json(['photo_path' => $profile->photo_path]);
    }

    public function servePhoto(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $token       = $request->query('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken) {
            abort(403, 'Unauthorized');
        }

        $user    = $accessToken->tokenable;
        $profile = ApplicantProfile::where('user_id', $user->id)->first();
        $path    = $user->photo_path ?? $profile?->photo_path;

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        if ($user->google_avatar) {
            return redirect($user->google_avatar);
        }

        // Return transparent pixel so the browser doesn't log a 404 for non-applicant users
        return response(
            base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'),
            200,
            ['Content-Type' => 'image/gif']
        );
    }

    public function uploadDocuments(Request $request): JsonResponse
    {
        $pdfRules = 'nullable|file|mimes:pdf|mimetypes:application/pdf|max:5120';
        $request->validate([
            'pds'        => $pdfRules,
            'app_letter' => $pdfRules,
            'ipcr'       => $pdfRules,
            'coe'        => $pdfRules,
            'tor'        => $pdfRules,
        ]);

        $profile = $this->getOrCreateProfile();
        $userId  = Auth::id();
        $dir     = "profile-documents/{$userId}";

        $map = [
            'pds'        => ['path' => 'pds_path',        'ts' => 'pds_uploaded_at'],
            'app_letter' => ['path' => 'app_letter_path', 'ts' => 'app_letter_uploaded_at'],
            'ipcr'       => ['path' => 'ipcr_path',       'ts' => 'ipcr_uploaded_at'],
            'coe'        => ['path' => 'coe_path',        'ts' => 'coe_uploaded_at'],
            'tor'        => ['path' => 'tor_path',        'ts' => 'tor_uploaded_at'],
        ];

        foreach ($map as $input => $cols) {
            if ($request->hasFile($input)) {
                $pathCol = $cols['path'];
                if ($profile->$pathCol) {
                    Storage::disk('local')->delete($profile->$pathCol);
                }
                $profile->$pathCol = $request->file($input)->store($dir, 'local');
                if ($cols['ts']) {
                    $profile->{$cols['ts']} = now();
                }
            }
        }

        $profile->save();

        $returnCols = ['pds_path', 'pds_uploaded_at', 'app_letter_path', 'app_letter_uploaded_at',
                       'ipcr_path', 'ipcr_uploaded_at', 'coe_path', 'coe_uploaded_at', 'tor_path', 'tor_uploaded_at'];

        return response()->json([
            'profile'     => $profile->only($returnCols),
            'is_complete' => $profile->isComplete(),
        ]);
    }

    // Work Experience ─────────────────────────────────────────────────────────

    public function storeExperience(Request $request): JsonResponse
    {
        $data = $request->validate([
            'position_title'     => 'required|string|max:200',
            'department_agency'  => 'required|string|max:200',
            'monthly_salary'     => 'nullable|numeric|min:0',
            'salary_grade'       => 'nullable|string|max:10',
            'appointment_status' => 'nullable|string|max:50',
            'government_service' => 'nullable|boolean',
            'date_from'          => 'required|string|max:20',
            'date_to'            => 'nullable|string|max:20',
            'is_present'         => 'nullable|boolean',
        ]);

        $exp = $this->getOrCreateProfile()->workExperiences()->create($data);

        return response()->json($exp, 201);
    }

    public function updateExperience(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'position_title'     => 'required|string|max:200',
            'department_agency'  => 'required|string|max:200',
            'monthly_salary'     => 'nullable|numeric|min:0',
            'salary_grade'       => 'nullable|string|max:10',
            'appointment_status' => 'nullable|string|max:50',
            'government_service' => 'nullable|boolean',
            'date_from'          => 'required|string|max:20',
            'date_to'            => 'nullable|string|max:20',
            'is_present'         => 'nullable|boolean',
        ]);

        $exp = $this->getOrCreateProfile()->workExperiences()->findOrFail($id);
        $exp->update($data);
        return response()->json($exp);
    }

    public function deleteExperience(int $id): JsonResponse
    {
        $this->getOrCreateProfile()->workExperiences()->findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    // Education ───────────────────────────────────────────────────────────────

    public function storeEducation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'level'          => 'required|string|max:50',
            'school_name'    => 'required|string|max:200',
            'degree_course'  => 'nullable|string|max:200',
            'period_from'    => 'nullable|string|max:10',
            'period_to'      => 'nullable|string|max:10',
            'units_earned'   => 'nullable|string|max:20',
            'year_graduated' => 'nullable|string|max:4',
            'honors'         => 'nullable|string|max:200',
        ]);

        $edu = $this->getOrCreateProfile()->educationalAttainments()->create($data);

        return response()->json($edu, 201);
    }

    public function updateEducation(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'level'          => 'required|string|max:50',
            'school_name'    => 'required|string|max:200',
            'degree_course'  => 'nullable|string|max:200',
            'period_from'    => 'nullable|string|max:10',
            'period_to'      => 'nullable|string|max:10',
            'units_earned'   => 'nullable|string|max:20',
            'year_graduated' => 'nullable|string|max:4',
            'honors'         => 'nullable|string|max:200',
        ]);

        $edu = $this->getOrCreateProfile()->educationalAttainments()->findOrFail($id);
        $edu->update($data);
        return response()->json($edu);
    }

    public function deleteEducation(int $id): JsonResponse
    {
        $this->getOrCreateProfile()->educationalAttainments()->findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    // Training ────────────────────────────────────────────────────────────────

    public function storeTraining(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'date_from'    => 'required|string|max:20',
            'date_to'      => 'nullable|string|max:20',
            'hours'        => 'nullable|numeric|min:0',
            'ld_type'      => 'nullable|string|max:50',
            'conducted_by' => 'nullable|string|max:200',
        ]);

        $training = $this->getOrCreateProfile()->trainings()->create($data);

        return response()->json($training, 201);
    }

    public function updateTraining(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'date_from'    => 'required|string|max:20',
            'date_to'      => 'nullable|string|max:20',
            'hours'        => 'nullable|numeric|min:0',
            'ld_type'      => 'nullable|string|max:50',
            'conducted_by' => 'nullable|string|max:200',
        ]);

        $training = $this->getOrCreateProfile()->trainings()->findOrFail($id);
        $training->update($data);
        return response()->json($training);
    }

    public function deleteTraining(int $id): JsonResponse
    {
        $this->getOrCreateProfile()->trainings()->findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    // Document serving ────────────────────────────────────────────────────────

    public function serveDocument(Request $request, string $path): \Symfony\Component\HttpFoundation\Response
    {
        $token = $request->query('token');
        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken) {
            abort(403, 'Unauthorized');
        }

        $user = $accessToken->tokenable;

        // Ownership check: path must be profile-documents/{userId}/...
        if (! str_starts_with($path, "profile-documents/{$user->id}/")) {
            abort(403, 'Access denied');
        }

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }

    // Helpers ─────────────────────────────────────────────────────────────────

    private function getOrCreateProfile(): ApplicantProfile
    {
        $user = Auth::user();

        return ApplicantProfile::firstOrCreate(['user_id' => $user->id]);
    }
}
