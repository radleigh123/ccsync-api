<?php

namespace App\Http\Requests\Requirement;

use App\Enums\RequirementStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComplianceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status'        => ['required', Rule::enum(RequirementStatus::class)],
            'verified_at'   => 'required|date',
            'verified_by'   => 'required|integer',
        ];
    }
}
