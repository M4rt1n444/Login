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
}
