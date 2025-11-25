<?php

namespace App\Http\Requests\Member;

use App\Enums\Gender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'email'         => 'sometimes|email|min:6|max:255',
            'display_name'  => 'sometimes|string|max:255',

            'phone'         => 'sometimes|starts_with:+',
            'gender'        => [Rule::enum(Gender::class)],
            'biography'     => 'sometimes|string',
        ];
    }

    public function messages(): array
    {
        return [
            'gender.enum' => 'The only valid genders are [MALE, FEMALE, OTHER].',
        ];
    }
}
