<?php

namespace App\Http\Resources\LeaveType;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'annual_allowance' => $this->annual_allowance,
            'requires_approval' => $this->requires_approval,
            'minimum_notice_days' => $this->minimum_notice_days,
            'maximum_days_per_request' => $this->maximum_days_per_request,
            'carried_forward' => $this->carried_forward,
            'carry_forward_max_days' => $this->carry_forward_max_days,
            'requires_attachment' => $this->requires_attachment,
            'half_day_allowed' => $this->half_day_allowed,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
