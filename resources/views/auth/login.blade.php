@extends('layouts.app')
@section('title', 'Iniciar sesión')

@section('content')
    <h1>Iniciar sesión</h1>

    @if (session('status'))
        <div class="alert-ok">{{ session('status') }}</div>
    @endif

    @error('credentials')
        <div class="alert-error">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('login.store') }}" novalidate>
        @csrf

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}">
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password">
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <button type="submit">Entrar</button>
    </form>

    <div class="link">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></div>
@endsection
