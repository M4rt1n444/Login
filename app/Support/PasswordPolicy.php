<?php

namespace App\Support;

/**
 * Comparte las reglas de contraseña entre registro y recuperación de cuenta.
 */
final class PasswordPolicy
{
    /**
     * Reglas aplicadas a la contraseña y su campo de confirmación.
     *
     * @return array<int, string>
     */
    public static function validationRules(): array
    {
        return [
            'required',
            'string',
            'min:8',
            'confirmed',
            'regex:/\p{Lu}/u',
            'regex:/\p{Ll}/u',
            'regex:/\p{N}/u',
        ];
    }

    /**
     * Mensajes legibles para los errores de la política compartida.
     *
     * @return array<string, string>
     */
    public static function validationMessages(): array
    {
        return [
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir al menos una mayúscula, una minúscula y un número.',
            'password.confirmed' => 'La contraseña y su confirmación no coinciden.',
        ];
    }
}
