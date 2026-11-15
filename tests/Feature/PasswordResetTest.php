<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Suite de Pruebas de Integración y Feature para HU-03: Recuperación de Contraseña (CP-15 a CP-21).
 * Implementa el patrón Arrange - Act - Assert (AAA) en cada caso de prueba.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HU-03: RECUPERACIÓN DE CONTRASEÑA (CP-15 a CP-21)
    // =========================================================================

    /**
     * CP-15 | HU-03 | CA-03.1 | Positivo
     * Solicitar recuperación con ana@test.local -> la solicitud se procesa sin errores y confirma envío.
     */
    public function test_cp15_solicitar_recuperacion_con_datos_validos(): void
    {
        // 1. Arrange
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@test.local']);

        // 2. Act
        $response = $this->post(route('password.email'), ['email' => 'ana@test.local']);

        // 3. Assert
        $response->assertSessionHas(
            'status',
            'Si la cuenta existe, recibirás un enlace para restablecer tu contraseña.'
        );
        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseCount('password_reset_tokens', 1);
    }

    /**
     * CP-16 | HU-03 | CA-03.2, CA-03.3 | Seguridad
     * ana@test.local (existente) y noexiste@test.local (inexistente) -> el mismo mensaje en ambos casos;
     * la notificación solo se envía al correo registrado.
     */
    public function test_cp16_mismo_mensaje_correo_existente_y_no_existente(): void
    {
        // 1. Arrange
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@test.local']);

        // 2. Act
        $respExistente = $this->post(route('password.email'), ['email' => 'ana@test.local']);
        $respInexistente = $this->post(route('password.email'), ['email' => 'noexiste@test.local']);

        // 3. Assert
        $respExistente->assertSessionHas(
            'status',
            'Si la cuenta existe, recibirás un enlace para restablecer tu contraseña.'
        );
        $respInexistente->assertSessionHas(
            'status',
            'Si la cuenta existe, recibirás un enlace para restablecer tu contraseña.'
        );

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseCount('password_reset_tokens', 1);
    }

    /**
     * CP-17 | HU-03 | CA-03.3 | Seguridad
     * Se envía ResetPassword a ana@test.local; el token es largo/aleatorio y en password_reset_tokens
     * se guarda hasheado, no igual al token del enlace; el enlace sirve una sola vez.
     */
    public function test_cp17_token_aleatorio_seguro_hasheado_y_de_un_solo_uso(): void
    {
        // 1. Arrange
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $this->post(route('password.email'), ['email' => 'ana@test.local']);
        $tokenEnlace = Notification::sent($user, ResetPassword::class)->first()->token;

        // 2. Act
        $registroBd = DB::table('password_reset_tokens')->where('email', 'ana@test.local')->first();

        // 3. Assert: Token seguro, largo y hasheado en BD
        $this->assertNotNull($registroBd);
        $this->assertGreaterThanOrEqual(32, strlen($tokenEnlace));
        $this->assertNotEquals($tokenEnlace, $registroBd->token);
        $this->assertTrue(Hash::check($tokenEnlace, $registroBd->token));

        // Act & Assert: Usar el token una vez con éxito
        $this->post(route('password.update'), [
            'token' => $tokenEnlace,
            'email' => 'ana@test.local',
            'password' => 'Nuev1234',
            'password_confirmation' => 'Nuev1234',
        ])->assertRedirect(route('login'));

        // Intento de reutilizar el mismo enlace -> rechazado
        $reuso = $this->post(route('password.update'), [
            'token' => $tokenEnlace,
            'email' => 'ana@test.local',
            'password' => 'Otra1234',
            'password_confirmation' => 'Otra1234',
        ]);
        $reuso->assertSessionHasErrors('email');
    }

    /**
     * CP-18 | HU-03 | CA-03.4 | Límite
     * A los 29 min el reset funciona; a los 31 min falla.
     * La expiración se valida al enviar el POST de password.update.
     */
    public function test_cp18_expiracion_token_29_min_funciona_31_min_falla(): void
    {
        // 1. Arrange
        Notification::fake();
        $user1 = User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $user2 = User::factory()->create(['email' => 'segundo@test.local', 'password' => 'Abcd1234']);

        $this->post(route('password.email'), ['email' => 'ana@test.local']);
        $token1 = Notification::sent($user1, ResetPassword::class)->first()->token;

        $this->post(route('password.email'), ['email' => 'segundo@test.local']);
        $token2 = Notification::sent($user2, ResetPassword::class)->first()->token;

        // 2. Act: Viajar 29 minutos en el tiempo
        $this->travel(29)->minutes();
        $resp29 = $this->post(route('password.update'), [
            'token' => $token1,
            'email' => 'ana@test.local',
            'password' => 'Nuev1234',
            'password_confirmation' => 'Nuev1234',
        ]);

        // 3. Assert (29 minutos -> válido)
        $resp29->assertRedirect(route('login'))
            ->assertSessionHas('status');
        $user1->refresh();
        $this->assertTrue(Hash::check('Nuev1234', $user1->password));

        // 2. Act: Avanzar 2 minutos más (total 31 min para el segundo token)
        $this->travel(2)->minutes();
        $resp31 = $this->post(route('password.update'), [
            'token' => $token2,
            'email' => 'segundo@test.local',
            'password' => 'Nuev1234',
            'password_confirmation' => 'Nuev1234',
        ]);

        // 3. Assert (31 minutos -> expirado)
        $resp31->assertSessionHasErrors('email');
        $user2->refresh();
        $this->assertTrue(Hash::check('Abcd1234', $user2->password));
    }

    /**
     * CP-19 | HU-03 | CA-03.5 | Límite
     * Abc1234 (7) rechazada; Nuev1234 con confirmación Nuev9999 rechazada; Nuev1234 con confirmación igual aceptada.
     * Utiliza un data provider.
     */
    #[DataProvider('cp19PasswordProvider')]
    public function test_cp19_politica_contrasenas_en_recuperacion(string $password, string $confirmation, bool $esValida): void
    {
        // 1. Arrange
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $this->post(route('password.email'), ['email' => 'ana@test.local']);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;

        // 2. Act
        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'ana@test.local',
            'password' => $password,
            'password_confirmation' => $confirmation,
        ]);

        // 3. Assert
        if ($esValida) {
            $response->assertRedirect(route('login'))
                ->assertSessionHas('status');
            $user->refresh();
            $this->assertTrue(Hash::check($password, $user->password));
        } else {
            $response->assertSessionHasErrors('password');
            $user->refresh();
            $this->assertTrue(Hash::check('Abcd1234', $user->password));
        }
    }

    public static function cp19PasswordProvider(): array
    {
        return [
            'CP-19 Límite 7 caracteres (Abc1234)' => ['Abc1234', 'Abc1234', false],
            'CP-19 Confirmación diferente (Nuev1234 / Nuev9999)' => ['Nuev1234', 'Nuev9999', false],
            'CP-19 Válida 8 caracteres (Nuev1234 / Nuev1234)' => ['Nuev1234', 'Nuev1234', true],
        ];
    }

    /**
     * CP-20 | HU-03 | CA-03.6 | Seguridad
     * Tras cambiar la contraseña, reutilizar el mismo token es rechazado y el registro del token ya no existe.
     */
    public function test_cp20_token_invalido_tras_cambio_y_registro_eliminado(): void
    {
        // 1. Arrange
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $this->post(route('password.email'), ['email' => 'ana@test.local']);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;

        // 2. Act: Primer reseteo exitoso
        $primerReset = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'ana@test.local',
            'password' => 'Nuev1234',
            'password_confirmation' => 'Nuev1234',
        ]);
        $primerReset->assertRedirect(route('login'));

        // Intento de reusar el mismo token
        $reusoReset = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'ana@test.local',
            'password' => 'Otra1234',
            'password_confirmation' => 'Otra1234',
        ]);

        // 3. Assert
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'ana@test.local']);
        $reusoReset->assertSessionHasErrors('email');
    }

    /**
     * CP-21 | HU-03 | CA-03.7 | Negativo
     * Después del reset, login con la clave anterior Abcd1234 falla y con la nueva Nuev1234 funciona.
     */
    public function test_cp21_contrasena_anterior_invalida_y_nueva_valida_tras_reset(): void
    {
        // 1. Arrange
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $this->post(route('password.email'), ['email' => 'ana@test.local']);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'ana@test.local',
            'password' => 'Nuev1234',
            'password_confirmation' => 'Nuev1234',
        ]);

        // 2. Act: Login con clave antigua Abcd1234
        $respAnterior = $this->post(route('login.store'), [
            'email' => 'ana@test.local',
            'password' => 'Abcd1234',
        ]);

        // 3. Assert: Clave antigua rechazada
        $respAnterior->assertSessionHasErrors('credentials');
        $this->assertGuest();

        // 2. Act: Login con nueva clave Nuev1234
        $respNueva = $this->post(route('login.store'), [
            'email' => 'ana@test.local',
            'password' => 'Nuev1234',
        ]);

        // 3. Assert: Nueva clave aceptada y acceso a dashboard
        $respNueva->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }
}
