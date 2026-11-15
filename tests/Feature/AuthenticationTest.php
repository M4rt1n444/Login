<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Suite de Pruebas de Integración y Feature para Autenticación:
 * HU-01 (Registro de usuarios: CP-01 a CP-08)
 * HU-02 (Inicio de sesión: CP-09 a CP-14)
 * HU-04 (Cierre de sesión: CP-22 a CP-25)
 * HU-05 (Límite de intentos fallidos: CP-26 a CP-30)
 *
 * Implementa el patrón Arrange - Act - Assert (AAA) en cada caso de prueba.
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

    // =========================================================================
    // HU-01: REGISTRO DE USUARIOS (CP-01 a CP-08)
    // =========================================================================

    /**
     * CP-01 | HU-01 | CA-01.6 | Positivo
     * Registrar ana@test.local con datos válidos -> usuario creado exitosamente.
     */
    public function test_cp01_registro_valido(): void
    {
        // 1. Arrange
        $datos = $this->datosValidos();

        // 2. Act
        $response = $this->post('/register', $datos);

        // 3. Assert
        $response->assertRedirect('/login')
            ->assertSessionHas('status');
        $this->assertDatabaseHas('users', ['email' => 'ana@test.local']);
    }

    /**
     * CP-02 | HU-01 | CA-01.2 | Negativo
     * Enviar todos los campos vacíos -> mensajes de obligatoriedad; no se crea usuario.
     */
    public function test_cp02_campos_vacios(): void
    {
        // 1. Arrange
        $datos = [];

        // 2. Act
        $response = $this->post('/register', $datos);

        // 3. Assert
        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * CP-03 | HU-01 | CA-01.3 | Negativo
     * Registrar correo ana@correo -> rechazado por formato inválido.
     */
    public function test_cp03_correo_formato_invalido(): void
    {
        // 1. Arrange
        $datos = $this->datosValidos(['email' => 'ana@correo']);

        // 2. Act
        $response = $this->post('/register', $datos);

        // 3. Assert
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * CP-04 | HU-01 | CA-01.3 | Negativo
     * Registrar correo existente ANA@test.local -> se rechaza el duplicado (case-insensitive).
     */
    public function test_cp04_correo_duplicado_sin_distinguir_mayusculas(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local']);
        $datosDuplicados = $this->datosValidos(['email' => 'ANA@test.local']);

        // 2. Act
        $response = $this->post('/register', $datosDuplicados);

        // 3. Assert
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    /**
     * CP-05 | HU-01 | CA-01.4 | Límite
     * Contraseña Abc1234 (7 caracteres) -> contraseña rechazada.
     */
    public function test_cp05_password_7_caracteres_rechazada(): void
    {
        // 1. Arrange
        $datos = $this->datosValidos([
            'password' => 'Abc1234',
            'password_confirmation' => 'Abc1234',
        ]);

        // 2. Act
        $response = $this->post('/register', $datos);

        // 3. Assert
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * CP-06 | HU-01 | CA-01.4 | Límite
     * Contraseña Abcd1234 (8 caracteres) -> contraseña aceptada, redirige al login y usuario creado.
     */
    public function test_cp06_password_8_caracteres_aceptada(): void
    {
        // 1. Arrange
        $datos = $this->datosValidos([
            'password' => 'Abcd1234',
            'password_confirmation' => 'Abcd1234',
        ]);

        // 2. Act
        $response = $this->post('/register', $datos);

        // 3. Assert
        $response->assertRedirect('/login')
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'ana@test.local']);
    }

    /**
     * CP-07 | HU-01 | CA-01.5 | Negativo
     * Contraseña y confirmación diferentes -> registro rechazado.
     */
    public function test_cp07_confirmacion_diferente(): void
    {
        // 1. Arrange
        $datos = $this->datosValidos(['password_confirmation' => 'Otra1234']);

        // 2. Act
        $response = $this->post('/register', $datos);

        // 3. Assert
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * CP-08 | HU-01 | CA-01.7 | Seguridad
     * Verificar que el valor almacenado en BD no es texto plano y coincide con el hash (incluye password_hash).
     */
    public function test_cp08_password_almacenada_con_hash(): void
    {
        // 1. Arrange
        $datos = $this->datosValidos();

        // 2. Act
        $this->post('/register', $datos);
        $user = User::where('email', 'ana@test.local')->first();

        // 3. Assert
        $this->assertNotNull($user);
        $this->assertNotEquals('Abcd1234', $user->password);
        $this->assertTrue(Hash::check('Abcd1234', $user->password));
        $this->assertTrue(Hash::check('Abcd1234', $user->password_hash));
    }

    // =========================================================================
    // HU-02: INICIO DE SESIÓN (CP-09 a CP-14)
    // =========================================================================

    /**
     * CP-09 | HU-02 | CA-02.2, CA-02.5 | Positivo
     * Correo y contraseña correctos -> acceso concedido al área privada.
     */
    public function test_cp09_login_correcto(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $credenciales = ['email' => 'ana@test.local', 'password' => 'Abcd1234'];

        // 2. Act
        $response = $this->post('/login', $credenciales);

        // 3. Assert
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * CP-10 | HU-02 | CA-02.3 | Negativo
     * Correo correcto, contraseña incorrecta (Abcd0000) -> acceso denegado con mensaje genérico.
     */
    public function test_cp10_password_incorrecta_mensaje_generico(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $credenciales = ['email' => 'ana@test.local', 'password' => 'Abcd0000'];

        // 2. Act
        $response = $this->post('/login', $credenciales);

        // 3. Assert
        $response->assertSessionHasErrors('credentials');
        $this->assertGuest();
        $this->assertSame(
            'Las credenciales ingresadas son incorrectas.',
            session('errors')->first('credentials')
        );
    }

    /**
     * CP-11 | HU-02 | CA-02.3 | Negativo
     * Correo no registrado -> acceso denegado con el MISMO mensaje genérico que CP-10.
     */
    public function test_cp11_correo_no_registrado_mismo_mensaje(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd0000']);
        $mensajeCp10 = session('errors')->first('credentials');

        // 2. Act
        $response = $this->post('/login', [
            'email' => 'noexiste@test.local',
            'password' => 'Abcd1234',
        ]);

        // 3. Assert
        $response->assertSessionHasErrors('credentials');
        $this->assertGuest();
        $mensajeCp11 = session('errors')->first('credentials');
        $this->assertSame($mensajeCp10, $mensajeCp11);
        $this->assertSame('Las credenciales ingresadas son incorrectas.', $mensajeCp11);
    }

    /**
     * CP-12 | HU-02 | CA-02.4 | Negativo
     * Ambos campos vacíos -> validaciones visibles; sin autenticación.
     */
    public function test_cp12_campos_vacios(): void
    {
        // 1. Arrange
        $credenciales = [];

        // 2. Act
        $response = $this->post('/login', $credenciales);

        // 3. Assert
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * CP-13 | HU-02 | CA-02.6 | Seguridad
     * Abrir URL privada sin iniciar sesión -> acceso denegado y redirección al login.
     */
    public function test_cp13_url_privada_sin_sesion_redirige_a_login(): void
    {
        // 1. Arrange (visitante no autenticado)

        // 2. Act
        $response = $this->get('/dashboard');

        // 3. Assert
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * CP-14 | HU-02 | CA-02.5 | Positivo
     * Iniciar sesión y navegar al área privada -> sesión válida y acceso autorizado 200 sin pedir login otra vez.
     */
    public function test_cp14_usuario_autenticado_accede_al_area_privada(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);

        // 2. Act
        $loginResponse = $this->post('/login', [
            'email' => 'ana@test.local',
            'password' => 'Abcd1234',
        ]);
        $dashboardResponse = $this->get('/dashboard');

        // 3. Assert
        $loginResponse->assertRedirect('/dashboard');
        $dashboardResponse->assertOk()
            ->assertSee('Usuarios registrados');
        $this->assertAuthenticated();
    }

    // =========================================================================
    // HU-04: CIERRE DE SESIÓN (CP-22 a CP-25)
    // =========================================================================

    /**
     * CP-22 | HU-04 | CA-04.1 | Positivo
     * Autenticado, el área privada contiene la opción/botón para cerrar sesión (formulario y ruta logout).
     */
    public function test_cp22_area_privada_contiene_opcion_cerrar_sesion(): void
    {
        // 1. Arrange
        $user = User::factory()->create(['email' => 'ana@test.local']);

        // 2. Act
        $response = $this->actingAs($user)->get('/dashboard');

        // 3. Assert
        $response->assertOk()
            ->assertSee(route('logout'))
            ->assertSee('Cerrar sesión');
    }

    /**
     * CP-23 | HU-04 | CA-04.2 | Seguridad
     * Tras logout la sesión queda invalidada: assertGuest, token CSRF regenerado y dashboard inaccesible.
     * Nota de entorno: con SESSION_DRIVER=array en tests, la persistencia en tabla 'sessions' no aplica;
     * la invalidación se audita mediante el guard de autenticación y la rotación del token de sesión.
     */
    public function test_cp23_cierre_sesion_invalida_sesion_y_regenera_token(): void
    {
        // 1. Arrange
        $user = User::factory()->create(['email' => 'ana@test.local']);
        $this->actingAs($user)->get('/dashboard');
        $tokenAntes = session()->token();

        // 2. Act
        $response = $this->post('/logout');

        // 3. Assert
        $this->assertGuest();
        $this->assertNotEquals($tokenAntes, session()->token());
        $this->get('/dashboard')->assertRedirect('/login');
    }

    /**
     * CP-24 | HU-04 | CA-04.3 | Positivo
     * POST logout -> redirige a la página de login.
     */
    public function test_cp24_post_logout_redirige_a_login(): void
    {
        // 1. Arrange
        $user = User::factory()->create(['email' => 'ana@test.local']);

        // 2. Act
        $response = $this->actingAs($user)->post('/logout');

        // 3. Assert
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * CP-25 | HU-04 | CA-04.4 | Seguridad
     * Tras logout, GET a la URL privada redirige al login.
     * Para prevención de visualización por botón Atrás del navegador, verifica cabecera Cache-Control.
     */
    public function test_cp25_navegacion_privada_y_cabeceras_cache_control_tras_logout(): void
    {
        // 1. Arrange
        $user = User::factory()->create(['email' => 'ana@test.local']);
        $authResponse = $this->actingAs($user)->get('/dashboard');
        $this->post('/logout');

        // 2. Act
        $guestResponse = $this->get('/dashboard');

        // 3. Assert
        $guestResponse->assertRedirect('/login');
        $this->assertGuest();

        // Verificación de directiva Cache-Control (CA-04.4 - Mitigación botón Atrás / bfcache)
        $cacheControl = (string) $authResponse->headers->get('Cache-Control');
        $this->assertStringContainsString(
            'no-store',
            $cacheControl,
            'DEFECTO CA-04.4: La respuesta de la página privada carece de la directiva Cache-Control: no-store, permitiendo que el botón Atrás del navegador muestre la información protegida desde la caché (bfcache).'
        );
    }

    // =========================================================================
    // HU-05: LÍMITE DE INTENTOS FALLIDOS (CP-26 a CP-30 + Bonus IP)
    // =========================================================================

    /**
     * CP-26 | HU-05 | CA-05.1, CA-05.2 | Límite
     * 5 fallos con Abcd0000 muestran el mensaje normal; el 6.º intento (incluso con contraseña correcta Abcd1234) queda bloqueado.
     */
    public function test_cp26_bloqueo_despues_de_cinco_intentos_fallidos(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);

        // 2. Act & 3. Assert: 5 intentos fallidos muestran mensaje habitual
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'ana@test.local',
                'password' => 'Abcd0000',
            ]);
            $response->assertSessionHasErrors('credentials');
            $this->assertSame(
                'Las credenciales ingresadas son incorrectas.',
                session('errors')->first('credentials')
            );
        }

        // 2. Act & 3. Assert: 6º intento con clave correcta queda bloqueado
        $bloqueado = $this->post('/login', [
            'email' => 'ana@test.local',
            'password' => 'Abcd1234',
        ]);

        $bloqueado->assertSessionHasErrors('credentials');
        $this->assertStringContainsString(
            'Demasiados intentos fallidos',
            session('errors')->first('credentials')
        );
        $this->assertGuest();
    }

    /**
     * CP-27 | HU-05 | CA-05.2, CA-05.4 | Límite
     * Con el bloqueo activo, a los 5 y a los 14 min sigue bloqueado aunque la contraseña sea correcta.
     */
    public function test_cp27_bloqueo_permanece_activo_a_los_5_y_14_minutos(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd0000']);
        }

        // 2. Act: evaluar a los 5 minutos
        $this->travel(5)->minutes();
        $resp5 = $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd1234']);

        // 3. Assert (5 minutos)
        $resp5->assertSessionHasErrors('credentials');
        $this->assertStringContainsString(
            'Demasiados intentos fallidos',
            session('errors')->first('credentials')
        );
        $this->assertGuest();

        // 2. Act: evaluar a los 14 minutos (5 + 9)
        $this->travel(9)->minutes();
        $resp14 = $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd1234']);

        // 3. Assert (14 minutos)
        $resp14->assertSessionHasErrors('credentials');
        $this->assertStringContainsString(
            'Demasiados intentos fallidos',
            session('errors')->first('credentials')
        );
        $this->assertGuest();
    }

    /**
     * CP-28 | HU-05 | CA-05.3 | Seguridad
     * El mensaje de bloqueo es idéntico para cuenta existente (ana@test.local) e inexistente (noexiste@test.local);
     * no revela si la cuenta existe ni detalles técnicos o de base de datos.
     */
    public function test_cp28_mensaje_bloqueo_identico_para_cuenta_existente_e_inexistente(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);

        // Bloquear ana@test.local
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd0000']);
        }
        $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        $mensajeExistente = session('errors')->first('credentials');

        // Bloquear noexiste@test.local
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => 'noexiste@test.local', 'password' => 'Abcd0000']);
        }

        // 2. Act
        $this->post('/login', ['email' => 'noexiste@test.local', 'password' => 'Abcd1234']);
        $mensajeInexistente = session('errors')->first('credentials');

        // 3. Assert
        $this->assertSame($mensajeExistente, $mensajeInexistente);
        $this->assertStringContainsString('Demasiados intentos fallidos', $mensajeInexistente);
        $this->assertStringNotContainsString('SQL', $mensajeInexistente);
        $this->assertStringNotContainsString('database', $mensajeInexistente);
        $this->assertStringNotContainsString('user', strtolower($mensajeInexistente));
    }

    /**
     * CP-29 | HU-05 | CA-05.4 | Positivo
     * 16 min después del bloqueo se puede iniciar sesión y el acceso se concede.
     */
    public function test_cp29_desbloqueo_automatico_despues_de_16_minutos(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd0000']);
        }
        // Comprobar que está bloqueado inicialmente
        $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd1234'])
            ->assertSessionHasErrors('credentials');

        // 2. Act: transcurren 16 minutos (ventana de 15 min expira)
        $this->travel(16)->minutes();
        $response = $this->post('/login', [
            'email' => 'ana@test.local',
            'password' => 'Abcd1234',
        ]);

        // 3. Assert
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * CP-30 | HU-05 | CA-05.5 | Positivo
     * 4 fallos, login correcto, otros 4 fallos -> la cuenta NO se bloquea (el contador se reinició).
     */
    public function test_cp30_login_exitoso_reinicia_contador_y_permite_otros_cuatro_intentos_sin_bloqueo(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);

        // 4 intentos fallidos iniciales
        for ($i = 1; $i <= 4; $i++) {
            $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd0000'])
                ->assertSessionHasErrors('credentials');
        }

        // 1 login exitoso que limpia el contador
        $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd1234'])
            ->assertRedirect('/dashboard');
        $this->post('/logout');

        // 2. Act: 4 nuevos intentos fallidos consecutivos
        for ($i = 1; $i <= 4; $i++) {
            $resp = $this->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd0000']);

            // 3. Assert: cada uno muestra error estándar sin caer en bloqueo (que exige 5 consecutivos)
            $resp->assertSessionHasErrors('credentials');
            $this->assertSame(
                'Las credenciales ingresadas son incorrectas.',
                session('errors')->first('credentials')
            );
        }

        $this->assertGuest();
    }

    /**
     * CP-30 (Bonus) | HU-05 | CA-05.1 | Seguridad
     * El bloqueo aplica a la combinación cuenta + IP; otra IP no queda bloqueada.
     */
    public function test_cp30_bloqueo_por_ip_independiente_otra_ip_no_queda_bloqueada(): void
    {
        // 1. Arrange
        User::factory()->create(['email' => 'ana@test.local', 'password' => 'Abcd1234']);

        // Bloquear ana@test.local desde la IP 192.168.1.10 (5 fallos)
        for ($i = 1; $i <= 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.10'])
                ->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd0000']);
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.10'])
            ->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd1234'])
            ->assertSessionHasErrors('credentials');

        // 2. Act: intentar login desde una IP diferente (192.168.1.20) con credenciales válidas
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.20'])
            ->post('/login', ['email' => 'ana@test.local', 'password' => 'Abcd1234']);

        // 3. Assert
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }
}
