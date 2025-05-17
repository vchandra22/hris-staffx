<?php

namespace App\Http\Resources\LeaveRequest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
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
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
            'reason' => $this->reason,
            'leave_type' => $this->whenLoaded('leaveType', function() {
                return [
                    'id' => $this->leaveType->id,
                    'name' => $this->leaveType->name,
                    'description' => $this->leaveType->description,
                ];
            }),
            'leave_type_id' => $this->leave_type_id,
            'total_days' => $this->total_days,
            'half_day' => (bool) $this->half_day,
            'half_day_time' => $this->half_day_time ? $this->half_day_time->format('H:i:s') : null,
            'status' => $this->status,
            'approver' => $this->whenLoaded('approver', function() {
                return [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                    'email' => $this->approver->email,
                ];
            }),
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at ? $this->approved_at->format('Y-m-d H:i:s') : null,
            'rejection_reason' => $this->rejection_reason,
            'attachment_path' => $this->attachment_path,
            'attachment_type' => $this->attachment_type,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
