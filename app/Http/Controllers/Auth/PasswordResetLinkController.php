<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPasswordResetLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(SendPasswordResetLinkRequest $request): RedirectResponse
    {
        try {
            Password::sendResetLink($request->validated());
        } catch (Throwable $e) {
            Log::error('Fallo al enviar correo de recuperación: '.$e->getMessage());
        }

        return back()->with(
            'status',
            'Si la cuenta existe, recibirás un enlace para restablecer tu contraseña.'
        );
    }
}