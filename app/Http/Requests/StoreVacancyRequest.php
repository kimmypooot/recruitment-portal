<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Vacancy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // A vacancy opened on an IMIS plantilla item takes its title, item number,
        // grade, salary and office from IMIS (VacancyController::store), so none
        // are asked for. One entered by hand needs them, except that a Contract of
        // Service position has no item number or salary grade.
        $linked = $this->filled('imis_plantilla_item_id');
        $plantilla = ! $linked && $this->input('employment_type', 'plantilla') !== 'contract_of_service';

        return [
            'imis_plantilla_item_id' => [
                'nullable', 'integer',
                Rule::unique('vacancies', 'imis_plantilla_item_id')->whereNull('deleted_at'),
            ],
            'employment_type' => ['nullable', Rule::in(Vacancy::EMPLOYMENT_TYPES)],
            'position_title' => [$linked ? 'nullable' : 'required', 'string', 'max:255'],
            'plantilla_no' => [$plantilla ? 'required' : 'nullable', 'string', 'max:100'],
            'salary_grade' => [$plantilla ? 'required' : 'nullable', 'integer', 'between:1,33'],
            'place_of_assignment' => [$linked ? 'nullable' : 'required', 'string', Rule::in(Vacancy::placesOfAssignment())],
            'education_req' => 'required|string',
            'experience_req' => 'required|string',
            'training_req' => 'required|string',
            'eligibility_req' => 'required|string',
            'deadline_at' => 'required|date|after:today',
            'position_level' => 'required|string|in:Supervisory,Technical or Non-Supervisory,Administrative Support,Skills, Trades and Craft',
            'monthly_salary' => 'nullable|numeric|min:0',
            'is_anticipated_vacancy' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'imis_plantilla_item_id.unique' => 'A vacancy has already been opened on that IMIS position.',
        ];
    }
}
