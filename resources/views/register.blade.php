@extends('layouts.app')
@section('title', 'Crear cuenta')

@section('content')
    <h1>Crear cuenta</h1>

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <label for="name">Nombre</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}">
        @error('name') <div class="error">{{ $message }}</div> @enderror

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}">
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password">
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation">
        @error('password_confirmation') <div class="error">{{ $message }}</div> @enderror

        <button type="submit">Registrarme</button>
    </form>

    <div class="link">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></div>
@endsection
