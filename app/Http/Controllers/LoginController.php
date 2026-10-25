<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Previene session fixation
            $request->session()->regenerate();

            // CA-02.5: redirige a la página privada
            return redirect()->intended(route('dashboard'));
        }

        // CA-02.3: mensaje genérico, sin revelar qué dato falló
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['credentials' => 'Las credenciales ingresadas son incorrectas.']);
    }
}
