@extends('layouts.app')
@section('title', 'Recuperar contraseña')

@section('content')
    <h1>Recuperar contraseña</h1>
    <p>Ingresa tu correo electrónico y te enviaremos un enlace para cambiarla.</p>

    @if (session('status'))
        <div class="alert-ok">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}">
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <button type="submit">Enviar enlace de recuperación</button>
    </form>

    <div class="link"><a href="{{ route('login') }}">Volver al inicio de sesión</a></div>
@endsection
