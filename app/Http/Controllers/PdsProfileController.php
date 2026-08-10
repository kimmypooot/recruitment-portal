<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ApplicantProfile;
use App\Models\PdsOtherInfo;
use App\Models\PdsReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * CRUD for the CS Form No. 212 (Revised 2026) sections that the existing
 * ProfileController does not cover.
 *
 * ProfileController already owns work experience, education and trainings
 * (sections V, III and VII); this adds family background, children, civil
 * service eligibility, voluntary work, other information, references and the
 * page-4 declaration.
 *
 * The repeating sections share one set of endpoints keyed by section name,
 * because they are structurally identical — a list owned by the profile, with
 * a sort order.
 */
class PdsProfileController extends Controller
{
    /**
     * Repeating sections: relation name + validation rules.
     *
     * @return array<string, array{relation: string, rules: array<string, mixed>}>
     */
    private function sections(): array
    {
        return [
            'children' => [
                'relation' => 'children',
                'rules'    => [
                    'full_name'     => ['required', 'string', 'max:255'],
                    'date_of_birth' => ['nullable', 'date'],
                ],
            ],
            'eligibilities' => [
                'relation' => 'civilServiceEligibilities',
                'rules'    => [
                    'eligibility_name'    => ['required', 'string', 'max:255'],
                    'rating'              => ['nullable', 'string', 'max:255'],
                    'examination_date'    => ['nullable', 'date'],
                    'examination_place'   => ['nullable', 'string', 'max:255'],
                    'license_number'      => ['nullable', 'string', 'max:255'],
                    'license_valid_until' => ['nullable', 'date'],
                ],
            ],
            'voluntary-works' => [
                'relation' => 'voluntaryWorks',
                'rules'    => [
                    'organization_name_address' => ['required', 'string', 'max:500'],
                    'date_from'                 => ['nullable', 'date'],
                    'date_to'                   => ['nullable', 'date'],
                    'number_of_hours'           => ['nullable', 'numeric', 'min:0'],
                    'position_nature_of_work'   => ['nullable', 'string', 'max:255'],
                ],
            ],
            'other-info' => [
                'relation' => 'otherInfos',
                'rules'    => [
                    'kind'  => ['required', Rule::in(PdsOtherInfo::KINDS)],
                    'value' => ['required', 'string', 'max:500'],
                ],
            ],
            'references' => [
                'relation' => 'references',
                'rules'    => [
                    'name'                       => ['required', 'string', 'max:255'],
                    'office_residential_address' => ['nullable', 'string', 'max:500'],
                    'contact_no_or_email'        => ['nullable', 'string', 'max:255'],
                ],
            ],
        ];
    }

    /** Everything the PDS editor needs in one call. */
    public function show(Request $request): JsonResponse
    {
        $profile = $this->profile($request);
        $profile->load(ApplicantProfile::PDS_RELATIONS);

        return response()->json([
            'family_background' => $profile->familyBackground,
            'children'          => $profile->children,
            'eligibilities'     => $profile->civilServiceEligibilities,
            'voluntary_works'   => $profile->voluntaryWorks,
            'other_info'        => $profile->otherInfos,
            'references'        => $profile->references,
            'declaration'       => $profile->declaration,
        ]);
    }

    // --- Repeating sections -------------------------------------------------

    public function store(Request $request, string $section): JsonResponse
    {
        $definition = $this->section($section);
        $profile    = $this->profile($request);

        $data = $request->validate($definition['rules']);
        $data['sort_order'] = (int) $request->input('sort_order', $profile->{$definition['relation']}()->count());

        $record = $profile->{$definition['relation']}()->create($data);

        return response()->json($record, Response::HTTP_CREATED);
    }

    public function update(Request $request, string $section, int $id): JsonResponse
    {
        $definition = $this->section($section);
        $profile    = $this->profile($request);

        $record = $profile->{$definition['relation']}()->findOrFail($id);

        // Partial updates: only validate what was actually sent.
        $rules = array_intersect_key($definition['rules'], $request->all());
        $record->update($request->validate($rules ?: $definition['rules']));

        return response()->json($record->fresh());
    }

    public function destroy(Request $request, string $section, int $id): JsonResponse
    {
        $definition = $this->section($section);

        $this->profile($request)->{$definition['relation']}()->findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    // --- Single-record sections ---------------------------------------------

    public function updateFamilyBackground(Request $request): JsonResponse
    {
        $profile = $this->profile($request);

        $data = $request->validate([
            'spouse_surname'          => ['nullable', 'string', 'max:255'],
            'spouse_first_name'       => ['nullable', 'string', 'max:255'],
            'spouse_middle_name'      => ['nullable', 'string', 'max:255'],
            'spouse_name_extension'   => ['nullable', 'string', 'max:255'],
            'spouse_occupation'       => ['nullable', 'string', 'max:255'],
            'spouse_employer'         => ['nullable', 'string', 'max:255'],
            'spouse_business_address' => ['nullable', 'string', 'max:255'],
            'spouse_telephone'        => ['nullable', 'string', 'max:255'],
            'father_surname'          => ['nullable', 'string', 'max:255'],
            'father_first_name'       => ['nullable', 'string', 'max:255'],
            'father_middle_name'      => ['nullable', 'string', 'max:255'],
            'father_name_extension'   => ['nullable', 'string', 'max:255'],
            'mother_maiden_surname'    => ['nullable', 'string', 'max:255'],
            'mother_maiden_first_name' => ['nullable', 'string', 'max:255'],
            'mother_maiden_middle_name' => ['nullable', 'string', 'max:255'],
        ]);

        $record = $profile->familyBackground()->updateOrCreate([], $data);

        return response()->json($record);
    }

    public function updateDeclaration(Request $request): JsonResponse
    {
        $profile = $this->profile($request);

        $data = $request->validate([
            'q34a_related_third_degree'  => ['nullable', 'boolean'],
            'q34b_related_fourth_degree' => ['nullable', 'boolean'],
            'q34_details'                => ['nullable', 'string'],
            'q35a_guilty_admin_offense'  => ['nullable', 'boolean'],
            'q35a_details'               => ['nullable', 'string'],
            'q35b_criminally_charged'    => ['nullable', 'boolean'],
            'q35b_date_filed'            => ['nullable', 'date'],
            'q35b_case_status'           => ['nullable', 'string', 'max:255'],
            'q36_convicted'              => ['nullable', 'boolean'],
            'q36_details'                => ['nullable', 'string'],
            'q37_separated_from_service' => ['nullable', 'boolean'],
            'q37_details'                => ['nullable', 'string'],
            'q38a_election_candidate'    => ['nullable', 'boolean'],
            'q38a_details'               => ['nullable', 'string'],
            'q38b_resigned_to_campaign'  => ['nullable', 'boolean'],
            'q38b_details'               => ['nullable', 'string'],
            'q39_immigrant_status'       => ['nullable', 'boolean'],
            'q39_country'                => ['nullable', 'string', 'max:255'],
            'q40a_indigenous_group'      => ['nullable', 'boolean'],
            'q40a_group_name'            => ['nullable', 'string', 'max:255'],
            'q40b_pwd'                   => ['nullable', 'boolean'],
            'q40b_id_no'                 => ['nullable', 'string', 'max:255'],
            'q40c_solo_parent'           => ['nullable', 'boolean'],
            'q40c_id_no'                 => ['nullable', 'string', 'max:255'],
            'government_id_type'         => ['nullable', 'string', 'max:255'],
            'government_id_number'       => ['nullable', 'string', 'max:255'],
            'government_id_issuance_date'  => ['nullable', 'date'],
            'government_id_issuance_place' => ['nullable', 'string', 'max:255'],
            'date_accomplished'          => ['nullable', 'date'],
        ]);

        $record = $profile->declaration()->updateOrCreate([], $data);

        return response()->json($record);
    }

    // --- Helpers ------------------------------------------------------------

    private function profile(Request $request): ApplicantProfile
    {
        // Queried, not read off the cached relation accessor — see PdsController.
        $profile = $request->user()->applicantProfile()->first();

        if ($profile === null) {
            abort(Response::HTTP_NOT_FOUND, 'You do not have an applicant profile yet.');
        }

        return $profile;
    }

    /** @return array{relation: string, rules: array<string, mixed>} */
    private function section(string $section): array
    {
        $definition = $this->sections()[$section] ?? null;

        if ($definition === null) {
            abort(Response::HTTP_NOT_FOUND, "Unknown PDS section: {$section}.");
        }

        return $definition;
    }

    /** Exposed for the frontend so it can cap the References list. */
    public function limits(): JsonResponse
    {
        return response()->json([
            'references_required' => PdsReference::REQUIRED_COUNT,
            'other_info_kinds'    => PdsOtherInfo::KINDS,
        ]);
    }
}
