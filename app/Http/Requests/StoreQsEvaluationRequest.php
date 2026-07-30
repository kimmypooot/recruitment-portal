<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQsEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id'    => 'required|exists:applications,id',
            'education_meets'   => 'required|boolean',
            'experience_meets'  => 'required|boolean',
            'training_meets'    => 'required|boolean',
            'eligibility_meets' => 'required|boolean',
            'remarks'           => 'nullable|string|max:1000',
        ];
    }
}
