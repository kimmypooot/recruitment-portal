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
        ];
    }
}
