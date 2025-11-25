<?php

namespace App\Http\Requests\Event;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
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
            'name'                  => 'required|string|max:255',
            'description'           => 'sometimes|string',
            'venue'                 => 'required|string|max:255',
            'event_date'            => 'required|date|after:today',
            'time_from'             => 'required|date_format:H:i',
            'time_to'               => 'required|date_format:H:i|after:time_from',
            'registration_start'    => 'required|date|before_or_equal:event_date',
            'registration_end'      => 'required|date|after_or_equal:registration_start|before_or_equal:event_date',
            'max_participants'      => 'required|integer|min:1',
            'status'                => [Rule::enum(Status::class)],
        ];
    }

    public function messages()
    {
        return parent::messages();
    }
}
