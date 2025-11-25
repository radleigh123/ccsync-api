<?php

namespace App\Http\Requests\Member;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
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
            'user_id'           => 'required|integer|exists:users,id',
            'first_name'        => 'required|string|max:255',
            'middle_name'       => 'sometimes|string|max:255',
            'last_name'         => 'required|string|max:255',
            'suffix'            => 'nullable|string|max:50',
            'id_school_number'  => 'required|integer|unique:members,id_school_number',
            'birth_date'        => 'required|date',
            'enrollment_date'   => 'required|date',
            'program'           => 'required|string|exists:programs,code',
            'year'              => 'required|integer|between:1,4',
            'is_paid'           => 'required|boolean',
            'gender'            => [Rule::enum(Gender::class)],
            'biography'         => 'sometimes|string',
            'phone'             => 'sometimes|string',
            'semester_id'       => 'sometimes|integer|exists:semesters,id',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'          => 'Please specify a member to add.',
            'id_school_number.unique'   => 'This member\'s ID school number is already a member.',
            'program.exists'            => 'The selected program is invalid/unknown.',
            'gender.enum'               => 'The only valid genders are [MALE, FEMALE, OTHER].',
            // 'semester_id.required'      => 'Please specify a semester in which member is enrolled.',
            'semester_id.exists'        => 'The selected semester is invalid/unknown.',
        ];
    }
}
