<?php

namespace App\Http\Controllers;

use App\Models\ApplicantProfile;
use App\Models\WorkExperience;
use App\Models\EducationalAttainment;
use App\Models\Training;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $profile = $this->getOrCreateProfile();
        $profile->fill($data);

        if ($profile->isComplete() && $profile->profile_completed_at === null) {
            $profile->profile_completed_at = now();
        }

        $profile->save();

        return response()->json([
            'profile'     => $profile->fresh(['workExperiences', 'educationalAttainments', 'trainings']),
            'is_complete' => $profile->isComplete(),
        ]);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:3072',
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

        if (! $profile?->photo_path || ! Storage::disk('public')->exists($profile->photo_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($profile->photo_path);
    }

    public function uploadDocuments(Request $request): JsonResponse
    {
        $request->validate([
            'pds'        => 'nullable|file|mimes:pdf|max:5120',
            'app_letter' => 'nullable|file|mimes:pdf|max:5120',
            'ipcr'       => 'nullable|file|mimes:pdf|max:5120',
            'coe'        => 'nullable|file|mimes:pdf|max:5120',
            'tor'        => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $profile = $this->getOrCreateProfile();
        $userId  = Auth::id();
        $dir     = "profile-documents/{$userId}";

        $map = [
            'pds'        => 'pds_path',
            'app_letter' => 'app_letter_path',
            'ipcr'       => 'ipcr_path',
            'coe'        => 'coe_path',
            'tor'        => 'tor_path',
        ];

        foreach ($map as $input => $column) {
            if ($request->hasFile($input)) {
                if ($profile->$column) {
                    Storage::disk('public')->delete($profile->$column);
                }
                $profile->$column = $request->file($input)->store($dir, 'public');
            }
        }

        $profile->save();

        return response()->json([
            'profile'     => $profile->only(array_values($map)),
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

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    // Helpers ─────────────────────────────────────────────────────────────────

    private function getOrCreateProfile(): ApplicantProfile
    {
        $user = Auth::user();

        return ApplicantProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['first_name' => '', 'last_name' => $user->name]
        );
    }
}
