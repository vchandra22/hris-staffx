<?php

namespace App\Http\Requests\LeaveType;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class LeaveTypeUpdateRequest extends FormRequest
{
    public $validator;

    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes()
    {
        return [
            'name' => 'Nama Tipe Cuti',
            'description' => 'Deskripsi',
            'annual_allowance' => 'Jatah Tahunan',
            'requires_approval' => 'Perlu Persetujuan',
            'minimum_notice_days' => 'Minimal Hari Pemberitahuan',
            'maximum_days_per_request' => 'Maksimal Hari per Pengajuan',
            'carried_forward' => 'Bisa Carry Forward',
            'carry_forward_max_days' => 'Maksimal Carry Forward',
            'requires_attachment' => 'Perlu Lampiran',
            'half_day_allowed' => 'Boleh Setengah Hari',
        ];
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100|unique:m_leave_types,name,' . $this->route('id'),
            'description' => 'nullable|string',
            'annual_allowance' => 'nullable|numeric|min:0',
            'requires_approval' => 'sometimes|boolean',
            'minimum_notice_days' => 'nullable|integer|min:0',
            'maximum_days_per_request' => 'nullable|integer|min:1',
            'carried_forward' => 'nullable|boolean',
            'carry_forward_max_days' => 'nullable|integer|min:0',
            'requires_attachment' => 'nullable|boolean',
            'half_day_allowed' => 'nullable|boolean',
        ];
    }
}
