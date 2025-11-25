<?php

namespace App\Http\Requests\Event;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
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
            'name'                  => 'sometimes|string|max:255',
            'description'           => 'sometimes|string',
            'venue'                 => 'sometimes|string|max:255',
            'event_date'            => 'sometimes|date|after:today',
            'time_from'             => 'sometimes|date_format:H:i',
            'time_to'               => 'sometimes|date_format:H:i|after:time_from',
            'registration_start'    => 'sometimes|date|before_or_equal:event_date',
            'registration_end'      => 'sometimes|date|after_or_equal:registration_start|before_or_equal:event_date',
            'max_participants'      => 'sometimes|integer|min:1',
            'status'                => [Rule::enum(Status::class)],
        ];
    }
}
