<?php

namespace App\Http\Resources\Requirement;

use App\Enums\RequirementStatus;
use App\Http\Resources\Member\MemberResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class ComplianceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'status'        => $this->status,
            'attempt'       => $this->attempt,
            'verified_at'   => $this->verified_at,
            'verified_by'   => $this->verified_by,
            'notes'         => $this->notes,

            // Relations
            'offering'      => new OfferingResource($this->whenLoaded('offering')),
            'member'        => new MemberResource($this->whenLoaded('member')),
            'audits'        => new ComplianceAuditCollection($this->whenLoaded('audits')),
            'documents'     => new ComplianceDocumentCollection($this->whenLoaded('documents')),

            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
