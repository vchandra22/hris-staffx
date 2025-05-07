<?php

namespace App\Http\Resources\Position;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PositionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'list' => PositionResource::collection($this->collection),
            'meta' => [
                'links' => $this->getUrlRange(1, $this->lastPage()),
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem()
            ]
        ];
    }
}
