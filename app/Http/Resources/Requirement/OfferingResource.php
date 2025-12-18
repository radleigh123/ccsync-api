<?php

namespace App\Http\Resources\Requirement;

use App\Http\Resources\SemesterResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            // 'requirement_id'    => $this->requirement_id,
            'open_at'           => $this->open_at,
            'close_at'          => $this->close_at,
            'max_submissions'   => $this->max_submissions,
            'active'            => $this->active,

            'semester'          => new SemesterResource($this->whenLoaded('semester')),
            'requirement'       => new RequirementResource($this->whenLoaded('requirement')),
        ];
    }
}
