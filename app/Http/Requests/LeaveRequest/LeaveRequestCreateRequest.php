<?php

namespace App\Http\Requests\LeaveRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestCreateRequest extends FormRequest
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
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'reason' => 'Alasan',
            'leave_type_id' => 'Tipe Cuti',
            'total_days' => 'Total Hari',
            'half_day' => 'Setengah Hari',
            'half_day_time' => 'Waktu Setengah Hari',
            'attachment_path' => 'Lampiran',
            'attachment_type' => 'Tipe Lampiran',
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'leave_type_id' => 'required|uuid|exists:leave_types,id',
            'total_days' => 'nullable|numeric|min:0.5',
            'half_day' => 'nullable|boolean',
            'half_day_time' => 'nullable|required_if:half_day,true|date_format:H:i:s',
            'attachment_path' => 'nullable|string',
            'attachment_type' => 'nullable|string|required_with:attachment_path',
        ];
    }
}
