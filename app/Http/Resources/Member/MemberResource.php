<?php

namespace App\Http\Resources\Member;

use App\Http\Resources\Event\EventCollection;
use App\Http\Resources\SemesterResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Query parameter to signal the minimal resource (SemesterResource)
        $request->query->set('lite_semester', true);

        return [
            'id'                => $this->id,
            'first_name'        => $this->first_name,
            'middle_name'       => $this->middle_name,
            'last_name'         => $this->last_name,
            'suffix'            => $this->suffix,
            'id_school_number'  => $this->id_school_number,
            'birth_date'        => $this->birth_date->toDateString(),
            'enrollment_date'   => $this->enrollment_date->toDateString(),
            'program'           => $this->program,
            'year'              => $this->year,
            'is_paid'           => $this->is_paid,
            'gender'            => $this->gender,
            'biography'         => $this->biography,
            'phone'             => $this->phone,

            'user'              => new UserResource($this->whenLoaded('user')),
            'semester'          => new SemesterResource($this->whenLoaded('semester')),
            'events'            => new EventCollection($this->whenLoaded('events')),
            'registered_at'     => $this->when(
                $this->pivot !== null,
                fn() => $this->pivot->registered_at
            ),

            'created_at'        => $this->created_at->toDateString(),
            'updated_at'        => $this->updated_at->toDateString(),
        ];
    }
}
