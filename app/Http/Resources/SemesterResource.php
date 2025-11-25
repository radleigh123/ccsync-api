<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SemesterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Lite alternative if parent resource (e.g. MemberResource) just wants the semester title
        if ($request->boolean('lite_semester')) {
            return [
                'id'    => $this->id,
                'title' => $this->title,
            ];
        }

        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'date_start'    => $this->date_start->toDateString(),
            'date_end'      => $this->date_end->toDateString(),
            'status'        => $this->status,
        ];
    }
}
