@extends('layouts.app')
@section('title', 'Área privada')
@section('cardClass', 'card-wide')

@section('content')
    <h1>Usuarios registrados</h1>
    <p>Bienvenido, <strong>{{ auth()->user()->name }}</strong>.</p>

    @if ($users->isEmpty())
        <p>Aún no hay usuarios registrados.</p>
    @else
        <div class="table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Correo electrónico</th>
                        <th scope="col">Fecha de registro</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <nav class="pagination" aria-label="Paginación de usuarios">
            {{ $users->links() }}
        </nav>
    @endif
@endsection
