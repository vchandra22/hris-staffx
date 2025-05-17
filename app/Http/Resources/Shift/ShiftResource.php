<?php

namespace App\Http\Resources\Shift;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'employee' => $this->whenLoaded('employee', function() {
                return [
                    'id' => $this->employee->id,
                    'user' => [
                        'id' => $this->employee->user->id,
                        'name' => $this->employee->user->name,
                        'email' => $this->employee->user->email,
                        'photo' => $this->employee->user->photo,
                    ]
                ];
            }),
            'date' => $this->date ? $this->date->format('Y-m-d') : null,
            'start_time' => $this->start_time ? $this->start_time->format('H:i:s') : null,
            'end_time' => $this->end_time ? $this->end_time->format('H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
