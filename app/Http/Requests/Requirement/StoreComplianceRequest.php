<?php

namespace App\Http\Requests\Requirement;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplianceRequest extends FormRequest
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
            'offering_id'   => 'required|integer|exists:offerings,id',
            'member_id'     => 'required|integer|exists:members,id',
            'note'          => 'sometimes|string',
        ];
    }

    public function messages()
    {
        return [
            'offering_id.exists'    => 'The selected offering does not exist.',
            'member_id'             => 'The selected member is not yet registered to the organization or does not exist.'
        ];
    }
}
