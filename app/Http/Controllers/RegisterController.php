<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        // CA-01.7: el cast 'hashed' del modelo User aplica bcrypt automáticamente
        User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        // CA-01.6: mensaje de confirmación
        return redirect()->route('login')
            ->with('status', 'Tu cuenta fue creada exitosamente. Ya puedes iniciar sesión.');
    }
}
