<?php

namespace App\Http\Resources\Requirement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RequirementCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'requirements'  => $this->collection,
            'meta'          => ['count' => $this->collection->count()],
        ];
    }
}
