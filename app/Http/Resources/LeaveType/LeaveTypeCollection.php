<?php

namespace App\Http\Resources\LeaveType;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LeaveTypeCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'list' => LeaveTypeResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
            ],
        ];
    }
}
