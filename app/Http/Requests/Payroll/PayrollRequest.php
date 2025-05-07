<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayrollRequest extends FormRequest
{
    public $validator;

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return $this->createRules();
        }

        return $this->updateRules();
    }

    /**
     * Rules untuk create payroll
     */
    private function createRules(): array
    {
        return [
            'employee_id' => [
                'required',
                'string',
                'exists:m_employees,id',
                Rule::unique('m_payrolls')->where(function ($query) {
                    return $query->where('employee_id', $this->employee_id)
                        ->where('month', $this->month)
                        ->where('year', $this->year)
                        ->whereNull('deleted_at');
                }),
            ],
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'base_salary' => 'required|numeric|min:0|max:999999999.99',
            'overtime_hours' => 'nullable|numeric|min:0|max:999.99',
            'overtime_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                function ($attribute, $value, $fail) {
                    if (!empty($this->overtime_hours) && empty($value)) {
                        $fail('Overtime rate is required when overtime hours is provided.');
                    }
                },
            ],
            'deductions' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
                function ($attribute, $value, $fail) {
                    $overtimePay = ($this->overtime_hours ?? 0) * ($this->overtime_rate ?? 0);
                    $totalEarnings = $this->base_salary + $overtimePay;

                    if ($value > $totalEarnings) {
                        $fail('Deductions cannot be greater than total earnings (base salary + overtime pay).');
                    }
                },
            ],
        ];
    }

    /**
     * Rules untuk update payroll
     */
    private function updateRules(): array
    {
        return [
            'employee_id' => [
                'required',
                'uuid',
                'exists:m_employees,id',
                Rule::unique('m_payrolls')
                    ->where(function ($query) {
                        return $query->where('employee_id', $this->employee_id)
                            ->where('month', $this->month)
                            ->where('year', $this->year)
                            ->whereNull('deleted_at');
                    })
                    ->ignore($this->route('id'))
            ],
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'base_salary' => 'required|numeric|min:0|max:999999999.99',
            'overtime_hours' => 'nullable|numeric|min:0|max:999.99',
            'overtime_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                function ($attribute, $value, $fail) {
                    if (!empty($this->overtime_hours) && empty($value)) {
                        $fail('Overtime rate is required when overtime hours is provided.');
                    }
                },
            ],
            'deductions' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
                function ($attribute, $value, $fail) {
                    $overtimePay = ($this->overtime_hours ?? 0) * ($this->overtime_rate ?? 0);
                    $totalEarnings = $this->base_salary + $overtimePay;

                    if ($value > $totalEarnings) {
                        $fail('Deductions cannot be greater than total earnings (base salary + overtime pay).');
                    }
                },
            ],
        ];
    }
}
