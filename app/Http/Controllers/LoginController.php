<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $throttleKey = $this->throttleKey($request);

        // CA-05.1 y CA-05.2: límite de 5 intentos fallidos consecutivos en 15 minutos (900 seg)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            // CA-05.3: mensaje comprensible sin revelar información sensible
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'credentials' => "Demasiados intentos fallidos. Por favor, inténtalo de nuevo en {$minutes} minuto(s).",
                ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // CA-05.5: un inicio de sesión exitoso reinicia el contador de intentos
            RateLimiter::clear($throttleKey);
            $this->recordAttempt($request->input('email', ''), $request->ip(), true);

            // Previene session fixation
            $request->session()->regenerate();

            // CA-02.5: redirige a la página privada
            return redirect()->intended(route('dashboard'));
        }

        // Registra intento fallido y suma al contador con decaimiento de 15 minutos (900 seg)
        RateLimiter::hit($throttleKey, 15 * 60);
        $this->recordAttempt($request->input('email', ''), $request->ip(), false);

        // CA-02.3: mensaje genérico, sin revelar qué dato falló
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['credentials' => 'Las credenciales ingresadas son incorrectas.']);
    }

    /**
     * HU-04: Cerrar sesión
     * CA-04.1, CA-04.2 y CA-04.3: Invalida la sesión actual en el servidor y redirige al login.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Genera la clave única de limitación por combinación de cuenta (email) e IP (CA-05.1).
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email', '')).'|'.$request->ip());
    }

    /**
     * Registra el intento en la tabla 'login_attempts' si está disponible en la BD.
     */
    protected function recordAttempt(string $email, string $ip, bool $wasSuccess): void
    {
        try {
            DB::table('login_attempts')->insert([
                'email'        => mb_strtolower(trim($email)),
                'ip_address'   => $ip,
                'was_success'  => $wasSuccess ? 1 : 0,
                'attempted_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silencioso para no interrumpir el flujo si la tabla aún no se ha migrado
        }
    }
}

