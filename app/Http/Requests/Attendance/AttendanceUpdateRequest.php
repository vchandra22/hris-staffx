<?php

namespace App\Http\Requests\Attendance;

use App\Models\AttendanceModel;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
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
            'date' => 'Tanggal',
            'check_in' => 'Waktu Masuk',
            'check_out' => 'Waktu Keluar',
            'status' => 'Status',
            'late_minutes' => 'Keterlambatan (menit)',
            'early_leave_minutes' => 'Pulang Awal (menit)',
            'overtime_minutes' => 'Lembur (menit)',
            'lat' => 'Latitude',
            'lng' => 'Longitude',
            'notes' => 'Catatan',
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
            'employee_id' => 'sometimes|uuid|exists:m_employees,id',
            'date' => 'sometimes|date',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after_or_equal:check_in',
            'status' => 'sometimes|in:present,late,early_leave,absent,half_day',
            'late_minutes' => 'nullable|integer|min:0',
            'early_leave_minutes' => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ];
    }
}
