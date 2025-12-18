<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Member\MemberResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'display_name'      => $this->display_name,
            'email'             => $this->email,
            'email_verified'    => $this->email_verified,
            'id_school_number'  => $this->id_school_number,
            'roles'             => $this->getRoleNames(),
            'permissions'       => $this->getAllPermissions()->pluck('name'),

            'member'            => new MemberResource($this->whenLoaded('member')),
        ];
    }
}
