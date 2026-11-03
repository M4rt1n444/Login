@extends('layouts.app')
@section('title', 'Crear nueva contraseña')

@section('content')
    <h1>Crear nueva contraseña</h1>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email', $email) }}">
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label for="password">Nueva contraseña</label>
        <input id="password" type="password" name="password">
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <label for="password_confirmation">Confirmar nueva contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation">
        @error('password_confirmation') <div class="error">{{ $message }}</div> @enderror

        <button type="submit">Guardar nueva contraseña</button>
    </form>
@endsection
