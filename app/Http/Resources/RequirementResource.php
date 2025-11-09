<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'status' => $this->status,
            // 'offering' => new OfferingResource($this->whenLoaded('offering')),
            // 'member' => new MemberResource($this->whenLoaded('member')),
            // 'documents' => ComplianceDocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
