<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_title'      => 'required|string|max:255',
            'plantilla_no'        => 'required|string|max:100',
            'salary_grade'        => 'required|integer|between:1,33',
            'place_of_assignment' => 'required|string|max:255',
            'education_req'       => 'required|string',
            'experience_req'      => 'required|string',
            'training_req'        => 'required|string',
            'eligibility_req'     => 'required|string',
            'deadline_at'         => 'required|date|after:today',
            'position_level'      => 'required|string|in:Supervisory,Technical or Non-Supervisory,Administrative Support,Skills, Trades and Craft',
        ];
    }
}
