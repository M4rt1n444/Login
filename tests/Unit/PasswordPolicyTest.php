<?php

namespace Tests\Unit;

use App\Support\PasswordPolicy;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Pruebas Unitarias para la Política de Contraseñas (HU-01 y HU-03).
 * Implementa el patrón Arrange - Act - Assert (AAA).
 * Se ejecutan en consola mediante: php artisan test --testsuite=Unit
 */
class PasswordPolicyTest extends TestCase
{
    /**
     * Escenario 1: Contraseña válida que cumple con todos los criterios de seguridad.
     */
    public function test_contrasena_valida_cumple_todas_las_reglas(): void
    {
        // 1. Arrange (Preparar el escenario y datos)
        $password = 'Segura123';
        $rules = [
            'password' => array_filter(PasswordPolicy::validationRules(), fn($r) => $r !== 'confirmed'),
        ];

        // 2. Act (Ejecutar la acción a probar)
        $validator = Validator::make(['password' => $password], $rules);
        $resultado = $validator->passes();

        // 3. Assert (Validar si el resultado es verdadero o falso)
        $this->assertTrue($resultado, 'La contraseña debería ser válida.');
    }

    /**
     * Escenario 2: Contraseña inválida por longitud menor a 8 caracteres (CA-01.4).
     */
    public function test_contrasena_falla_si_tiene_menos_de_ocho_caracteres(): void
    {
        // 1. Arrange (Preparar datos: 7 caracteres)
        $password = 'Abc1234';
        $rules = [
            'password' => array_filter(PasswordPolicy::validationRules(), fn($r) => $r !== 'confirmed'),
        ];

        // 2. Act (Ejecutar la validación)
        $validator = Validator::make(['password' => $password], $rules);
        $resultado = $validator->passes();

        // 3. Assert (Validar que no pasa)
        $this->assertFalse($resultado, 'La contraseña con menos de 8 caracteres no debe ser aprobada.');
        $this->assertTrue($validator->errors()->has('password'));
    }

    /**
     * Escenario 3: Contraseña inválida si no contiene al menos una letra mayúscula (CA-01.4).
     */
    public function test_contrasena_falla_si_no_tiene_mayuscula(): void
    {
        // 1. Arrange (Preparar datos: solo minúsculas y números)
        $password = 'abcd1234';
        $rules = [
            'password' => array_filter(PasswordPolicy::validationRules(), fn($r) => $r !== 'confirmed'),
        ];

        // 2. Act (Ejecutar la validación)
        $validator = Validator::make(['password' => $password], $rules);
        $resultado = $validator->passes();

        // 3. Assert (Validar que es falso)
        $this->assertFalse($resultado, 'Una contraseña sin letra mayúscula debe ser rechazada.');
    }

    /**
     * Escenario 4: Contraseña inválida si no contiene al menos un número (CA-01.4).
     */
    public function test_contrasena_falla_si_no_tiene_numero(): void
    {
        // 1. Arrange (Preparar datos: solo letras sin números)
        $password = 'Abcdefgh';
        $rules = [
            'password' => array_filter(PasswordPolicy::validationRules(), fn($r) => $r !== 'confirmed'),
        ];

        // 2. Act (Ejecutar la validación)
        $validator = Validator::make(['password' => $password], $rules);
        $resultado = $validator->passes();

        // 3. Assert (Validar que es falso)
        $this->assertFalse($resultado, 'Una contraseña sin números debe ser rechazada.');
    }
}

