<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'check_in' => $this->check_in ? $this->check_in->format('Y-m-d H:i:s') : null,
            'check_out' => $this->check_out ? $this->check_out->format('Y-m-d H:i:s') : null,
            'status' => $this->status,
            'late_minutes' => (int) $this->late_minutes,
            'early_leave_minutes' => (int) $this->early_leave_minutes,
            'overtime_minutes' => (int) $this->overtime_minutes,
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
