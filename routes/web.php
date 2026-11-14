<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;


Route::redirect('/', '/login');

// Solo visitantes (no autenticados)
Route::middleware('guest')->group(function () {
    // HU-01: Registro
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // HU-02: Inicio de sesión
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // HU-03: recuperación de contraseña
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::get('/debug/users', function () {
    return response()->json(User::all());
});

// Área privada (CA-02.5 y CA-02.6)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UsersController::class, 'index'])->name('dashboard');

    // HU-04: Cerrar sesión (CA-04.1, CA-04.2, CA-04.3)
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

