<?php

namespace App\Http\Requests\Employee;

use App\Models\EmployeeModel;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
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
            'name' => 'Nama',
            'email' => 'Email',
            'phone_number' => 'Nomor Telepon',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'address' => 'Alamat',
            'department_id' => 'Departemen',
            'position_id' => 'Jabatan',
            'hire_date' => 'Tanggal Bergabung',
            'salary' => 'Gaji Pokok',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = EmployeeModel::with('user')->find($this->route('id'));

        return [
            // User validation
            'name' => 'sometimes|string|max:100',
            'email' => [
                'sometimes',
                'email',
                'max:50',
                Rule::unique('m_user', 'email')->ignore($employee?->user?->id),
            ],
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|string|max:100',
            'role_id' => 'nullable|uuid|exists:m_user_roles,id',

            // Employee validation
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'department_id' => 'sometimes|uuid|exists:m_departments,id',
            'position_id' => 'sometimes|uuid|exists:m_positions,id',
            'hire_date' => 'sometimes|date',
            'salary' => 'sometimes|numeric|min:0|decimal:0,2',
        ];
    }
}
