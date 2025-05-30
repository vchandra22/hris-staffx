<?php

namespace App\Http\Requests\Role;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public $validator;

    /**
     * Custom attribute names.
     */
    public function attributes()
    {
        return [
            'name' => 'Nama Role',
            'access' => 'Hak Akses',
        ];
    }

    /**
     * Handle failed validation.
     */
    public function failedValidation(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return $this->createRules();
        }

        return $this->updateRules();
    }

    private function createRules(): array
    {
        return [
            'name' => 'required|max:50|unique:m_user_roles,name',
            'access' => 'required|array',
        ];
    }

    private function updateRules(): array
    {
        return [
            'id' => 'required|exists:m_user_roles,id',
            'name' => 'required|max:50|unique:m_user_roles,name,' . $this->id,
            'access' => 'required|array',
        ];
    }
}
