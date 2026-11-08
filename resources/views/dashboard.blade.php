@extends('layouts.app')
@section('title', 'Área privada')
@section('cardClass', 'card-wide')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid #e5e7eb; padding-bottom: 16px;">
        <div>
            <h1 style="margin: 0;">Usuarios registrados</h1>
            <p style="margin: 4px 0 0; color: #4b5563;">Bienvenido, <strong>{{ auth()->user()->name }}</strong>.</p>
        </div>
        <!-- HU-04: Botón para cerrar sesión (CA-04.1) -->
        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
            @csrf
            <button type="submit" style="width: auto; margin-top: 0; background: #dc2626; color: #fff; padding: 8px 18px; border-radius: 6px; font-weight: 500; font-size: 0.9rem; cursor: pointer;">
                Cerrar sesión
            </button>
        </form>
    </div>

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
