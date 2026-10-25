@extends('layouts.app')
@section('title', 'Área privada')

@section('content')
    <h1>Área privada</h1>
    <p>Bienvenido, <strong>{{ auth()->user()->name }}</strong>.</p>
    <p>Esta página solo es visible para usuarios autenticados.</p>
@endsection
