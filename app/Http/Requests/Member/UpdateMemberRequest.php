<?php

namespace App\Http\Requests\Member;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
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
            'first_name'        => 'sometimes|string|max:255',
            'middle_name'       => 'sometimes|string|max:255',
            'last_name'         => 'sometimes|string|max:255',
            'suffix'            => 'nullable|string|max:50',
            'id_school_number'  => 'sometimes|integer|unique:members,id_school_number,{$id}',
            'birth_date'        => 'sometimes|date',
            'enrollment_date'   => 'sometimes|date',
            'program'           => 'sometimes|string|exists:programs,code',
            'year'              => 'sometimes|integer|between:1,4',
            'is_paid'           => 'nullable|boolean',
            'gender'            => [Rule::enum(Gender::class)],
            'biography'         => 'sometimes|string',
            'phone'             => 'sometimes|starts_with:+',
            'semester_id'       => 'sometimes|integer|exists:semesters,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id_school_number.unique'   => 'This member\'s ID school number is already a member.',
            'program.exists'            => 'The selected program is invalid/unknown.',
            'is_paid.boolean'           => 'Payment status must be true or false.',
            'gender.enum'               => 'The only valid genders are [MALE, FEMALE, OTHER].',
            'semester_id.exists'        => 'The selected semester is invalid/unknown.',
        ];
    }
}
