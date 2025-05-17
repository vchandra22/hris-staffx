<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'user' => $this->whenLoaded('user', function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone_number' => $this->user->phone_number,
                    'photo' => $this->user->photo,
                ];
            }, null),
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date ? $this->birth_date->format('Y-m-d') : null,
            'address' => $this->address,
            'department' => $this->whenLoaded('department', function() {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ];
            }, $this->whenLoaded('currentPosition', function() {
                return $this->currentPosition->department ? [
                    'id' => $this->currentPosition->department->id,
                    'name' => $this->currentPosition->department->name,
                ] : null;
            })),
            'position' => $this->whenLoaded('position', function() {
                return [
                    'id' => $this->position->id,
                    'name' => $this->position->name,
                ];
            }, $this->whenLoaded('currentPosition', function() {
                return $this->currentPosition->position ? [
                    'id' => $this->currentPosition->position->id,
                    'name' => $this->currentPosition->position->name,
                ] : null;
            })),
            'hire_date' => $this->hire_date ? $this->hire_date->format('Y-m-d') : null,
            'salary' => $this->whenLoaded('currentPosition', function() {
                return (float) $this->currentPosition->salary;
            }, (float) $this->current_salary),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
