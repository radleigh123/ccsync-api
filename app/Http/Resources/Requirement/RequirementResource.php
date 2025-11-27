<?php

namespace App\Http\Resources\Requirement;

use App\Http\Resources\SemesterResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequirementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $request->query->set('lite_semester', true);

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'type'          => $this->type,
            'is_active'     => $this->is_active,

            // Relationships
            'semester'      => new SemesterResource($this->whenLoaded('semester')),
            'offerings'     => new OfferingCollection($this->whenLoaded('offerings')),
            // 'member' => new MemberResource($this->whenLoaded('member')),
            // 'documents' => ComplianceDocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
