<?php

namespace App\Http\Resources\Event;

use App\Http\Resources\Member\MemberCollection;
use App\Http\Resources\SemesterResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'description'           => $this->description,
            'venue'                 => $this->venue,
            'event_date'            => $this->event_date->format('Y-m-d'),
            'time_from'             => $this->time_from->format('H:i:s'),
            'time_to'               => $this->time_to->format('H:i:s'),
            'registration_start'    => $this->registration_start->format('Y-m-d'),
            'registration_end'      => $this->registration_end->format('Y-m-d'),
            'max_participants'      => $this->max_participants,
            'status'                => $this->status,

            // Computed attributes
            'available_slots'       => $this->available_slots,
            'is_full'               => $this->is_full,
            'is_registration_open'  => $this->is_registration_open,
            'registration_due'      => $this->registration_due,
            'attendees'             => $this->attendees,

            // Relationships
            'members'               => new MemberCollection($this->whenLoaded('members')),
            'semester'              => new SemesterResource($this->whenLoaded('semester')),

            'created_at'            => $this->created_at->format('Y-m-d H:m:s'),
            'updated_at'            => $this->updated_at->format('Y-m-d H:m:s'),
        ];
    }
}
