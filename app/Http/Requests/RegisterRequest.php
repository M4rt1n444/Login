<?php

namespace App\Http\Requests;

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
            // CA-01.4: mínimo 8, mayúscula, minúscula y número
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/\p{Lu}/u', // al menos una mayúscula
                'regex:/\p{Ll}/u', // al menos una minúscula
                'regex:/\p{N}/u',  // al menos un número
            ],
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
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex'    => 'La contraseña debe incluir al menos una mayúscula, una minúscula y un número.',
            'password.confirmed' => 'La contraseña y su confirmación no coinciden.',
            'password_confirmation.required' => 'La confirmación de la contraseña es obligatoria.',
        ];
    }
}
