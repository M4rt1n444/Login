<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Cubre CP-01 a CP-14 del Plan de Pruebas (TP-S01).
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function datosValidos(array $override = []): array
    {
        return array_merge([
            'name' => 'Ana',
            'email' => 'ana@test.local',
            'password' => 'Abcd1234',
            'password_confirmation' => 'Abcd1234',
        ], $override);
    }

    // ---------- HU-01 ----------
    public function test_cp01_registro_valido(): void
    {
        $this->post('/register', $this->datosValidos())
            ->assertRedirect('/login')
            ->assertSessionHas('status');
        $this->assertDatabaseHas('users', ['email' => 'ana@test.local']);
    }

    public function test_cp02_campos_vacios(): void
    {
        $this->post('/register', [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_cp03_correo_formato_invalido(): void
    {
        $this->post('/register', $this->datosValidos(['email' => 'ana@correo']))
            ->assertSessionHasErrors('email');
    }

    public function test_cp04_correo_duplicado_sin_distinguir_mayusculas(): void
    {
        User::factory()->create(['email' => 'ana@test.local']);
        $this->post('/register', $this->datosValidos(['email' => 'ANA@test.local']))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_cp05_password_7_caracteres_rechazada(): void
    {
        $this->post('/register', $this->datosValidos(['password' => 'Abc1234', 'password_confirmation' => 'Abc1234']))
            ->assertSessionHasErrors('password');
    }

    public function test_cp06_password_8_caracteres_aceptada(): void
    {
        $this->post('/register', $this->datosValidos())
            ->assertSessionHasNoErrors();
    }

    public function test_cp07_confirmacion_diferente(): void
    {
        $this->post('/register', $this->datosValidos(['password_confirmation' => 'Otra1234']))
            ->assertSessionHasErrors('password');
    }

    public function test_cp08_password_almacenada_con_hash(): void
    {
        $this->post('/register', $this->datosValidos());
        $user = User::where('email', 'ana@test.local')->first();
        $this->assertNotEquals('Abcd1234', $user->password);
        $this->assertTrue(Hash::check('Abcd1234', $user->password));
    }

    // ---------- HU-02 ----------
    public function test_cp09_login_correcto(): void
    {
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd1234'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_cp10_password_incorrecta_mensaje_generico(): void
    {
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd1235'])
            ->assertSessionHasErrors('credentials');
        $this->assertGuest();
    }

    public function test_cp11_correo_no_registrado_mismo_mensaje(): void
    {
        $this->post('/login', ['email' => 'nadie@test.local', 'password' => 'Abcd1234'])
            ->assertSessionHasErrors('credentials');
        $this->assertGuest();
    }

    public function test_cp12_campos_vacios(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_cp13_url_privada_sin_sesion_redirige_a_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_cp14_usuario_autenticado_accede_al_area_privada(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk();
    }

    // ---------- HU-04: Cerrar sesión ----------
    public function test_cp15_usuario_autenticado_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_cp16_despues_de_cerrar_sesion_no_accede_a_area_privada(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_cp17_invitado_no_puede_hacer_logout(): void
    {
        $this->post('/logout')
            ->assertRedirect('/login');
    }

    // ---------- HU-05: Límite de intentos fallidos ----------
    public function test_cp18_bloqueo_despues_de_cinco_intentos_fallidos(): void
    {
        $user = User::factory()->create(['email' => 'bloqueo@test.local', 'password' => 'Abcd1234']);

        // 5 intentos fallidos consecutivos
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'bloqueo@test.local',
                'password' => 'Erronea999',
            ]);
            $response->assertSessionHasErrors('credentials');
        }

        // Intento 6 debe estar bloqueado por rate limiting
        $response = $this->post('/login', [
            'email' => 'bloqueo@test.local',
            'password' => 'Abcd1234', // Incluso con clave correcta
        ]);

        $response->assertSessionHasErrors('credentials');
        $this->assertGuest();
    }

    public function test_cp19_login_exitoso_reinicia_contador_de_intentos(): void
    {
        $user = User::factory()->create(['email' => 'reinicio@test.local', 'password' => 'Abcd1234']);

        // 3 intentos fallidos
        for ($i = 1; $i <= 3; $i++) {
            $this->post('/login', [
                'email' => 'reinicio@test.local',
                'password' => 'Erronea999',
            ]);
        }

        // Intento exitoso limpia el contador
        $this->post('/login', [
            'email' => 'reinicio@test.local',
            'password' => 'Abcd1234',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }
}
