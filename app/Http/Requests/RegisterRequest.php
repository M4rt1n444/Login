<?php

namespace App\Http\Requests;

use App\Support\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // CA-01.3: el correo se normaliza a minúsculas para que la unicidad
    // no distinga mayúsculas de minúsculas (ANA@test.local == ana@test.local)
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email:filter', 'max:255', 'unique:users,email'],
            // HU-01 y HU-03 comparten la misma política de contraseñas.
            'password' => PasswordPolicy::validationRules(),
            'password_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'El nombre es obligatorio.',
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El correo electrónico no tiene un formato válido.',
            'email.unique'      => 'Este correo electrónico ya está registrado.',
            'password_confirmation.required' => 'La confirmación de la contraseña es obligatoria.',
        ] + PasswordPolicy::validationMessages();
    }
}
