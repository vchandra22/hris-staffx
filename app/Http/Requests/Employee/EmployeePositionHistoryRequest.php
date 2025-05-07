<?php

namespace App\Http\Requests\Employee;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class EmployeePositionHistoryRequest extends FormRequest
{
    public $validator;

    /**
     * Tampilkan pesan error ketika validasi gagal
     *
     * @return void
     */
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
     * Setting custom attribute pesan error yang ditampilkan
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'employee_id' => 'Karyawan',
            'position_id' => 'Jabatan',
            'department_id' => 'Departemen',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'is_current' => 'Status Aktif',
            'salary' => 'Gaji',
            'notes' => 'Catatan',
            'approved_by' => 'Disetujui Oleh',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid|exists:m_employees,id',
            'position_id' => 'required|uuid|exists:m_positions,id',
            'department_id' => 'required|uuid|exists:m_departments,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'salary' => 'required|numeric|min:0|decimal:0,2',
            'notes' => 'nullable|string',
            'approved_by' => 'nullable|uuid|exists:m_user,id',
        ];
    }
}
