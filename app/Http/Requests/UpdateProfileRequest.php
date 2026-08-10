<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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

            // CS Form No. 212 (Revised 2026), Section I
            'place_of_birth'           => 'nullable|string|max:150',
            'civil_status_other'       => 'nullable|string|max:100',
            'height_m'                 => 'nullable|numeric|min:0|max:3',
            'weight_kg'                => 'nullable|numeric|min:0|max:999',
            'blood_type'               => 'nullable|string|max:10',
            'citizenship'              => 'nullable|string|in:filipino,dual',
            'citizenship_basis'        => 'nullable|string|in:by_birth,by_naturalization',
            'dual_citizenship_country' => 'nullable|string|max:100',

            'gsis_id_no'         => 'nullable|string|max:50',
            'pagibig_id_no'      => 'nullable|string|max:50',
            'philhealth_no'      => 'nullable|string|max:50',
            'sss_no'             => 'nullable|string|max:50',
            'tin_no'             => 'nullable|string|max:50',
            'umid_id_no'         => 'nullable|string|max:50',
            'philsys_number'     => 'nullable|string|max:50',
            'agency_employee_no' => 'nullable|string|max:50',

            'telephone_no' => 'nullable|string|max:50',

            'res_house_block_lot'   => 'nullable|string|max:150',
            'res_street'            => 'nullable|string|max:150',
            'res_subdivision'       => 'nullable|string|max:150',
            'res_barangay'          => 'nullable|string|max:150',
            'res_city_municipality' => 'nullable|string|max:150',
            'res_province'          => 'nullable|string|max:150',
            'res_zip_code'          => 'nullable|string|max:10',

            'perm_house_block_lot'   => 'nullable|string|max:150',
            'perm_street'            => 'nullable|string|max:150',
            'perm_subdivision'       => 'nullable|string|max:150',
            'perm_barangay'          => 'nullable|string|max:150',
            'perm_city_municipality' => 'nullable|string|max:150',
            'perm_province'          => 'nullable|string|max:150',
            'perm_zip_code'          => 'nullable|string|max:10',
        ];
    }
}
