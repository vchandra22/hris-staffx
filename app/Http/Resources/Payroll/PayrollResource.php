<?php

namespace App\Http\Resources\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => [
                'id' => $this->employee->id,
                'name' => $this->employee->user->name,
                'department' => [
                    'id' => $this->employee->department->id,
                    'name' => $this->employee->department->name,
                ],
                'position' => [
                    'id' => $this->employee->position->id,
                    'name' => $this->employee->position->name,
                ],
            ],
            'month' => $this->month,
            'year' => $this->year,
            'period' => sprintf('%s-%02d', $this->year, $this->month),
            'salary_details' => [
                'base_salary' => (float) $this->base_salary,
                'overtime' => [
                    'hours' => (float) $this->overtime_hours,
                    'rate' => (float) $this->overtime_rate,
                    'pay' => (float) $this->overtime_pay,
                ],
                'deductions' => (float) $this->deductions,
                'net_salary' => (float) $this->net_salary,
            ],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
