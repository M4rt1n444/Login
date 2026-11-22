@extends('layouts.app')
@section('title', 'Crear Nueva Tarea')
@section('cardClass', 'card-wide')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid #e5e7eb; padding-bottom: 14px;">
        <div>
            <h1 style="margin: 0; color: #1e3a8a;">➕ Crear Nueva Tarea</h1>
            <p style="margin: 4px 0 0; color: #6b7280; font-size: 0.9rem;">
                Ingresa los datos para registrar la tarea en tu organizador y calendario.
            </p>
        </div>
        <div>
            <a href="{{ route('tasks.index') }}" style="display: inline-block; padding: 8px 14px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.85rem; border: 1px solid #d1d5db;">
                &larr; Volver a Tareas
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-error" style="margin-bottom: 20px;">
            <strong>Por favor corrige los siguientes errores:</strong>
            <ul style="margin: 8px 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tasks.store') }}">
        @csrf

        {{-- Nombre de la tarea (Obligatorio) --}}
        <div style="margin-bottom: 16px;">
            <label for="name">Nombre de la tarea <span style="color: #dc2626;">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Ej: Entrega de Sprint 3" required>
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Descripción (Campo complementario) --}}
        <div style="margin-bottom: 16px;">
            <label for="description">Descripción (Opcional)</label>
            <textarea id="description" name="description" rows="3"
                      style="width: 100%; padding: 9px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 0.95rem;"
                      placeholder="Detalles adicionales sobre las actividades de la tarea...">{{ old('description') }}</textarea>
            @error('description')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Fechas de inicio y fin (Obligatorias) --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label for="start_date">Fecha de inicio <span style="color: #dc2626;">*</span></label>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                @error('start_date')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="end_date">Fecha de fin <span style="color: #dc2626;">*</span></label>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}" required>
                @error('end_date')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Estado de la tarea (Obligatorio: Pendiente, En progreso, Completada) --}}
        <div style="margin-bottom: 24px;">
            <label for="status">Estado de la tarea <span style="color: #dc2626;">*</span></label>
            <select id="status" name="status" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-size: 0.95rem; background: #fff;" required>
                <option value="Pendiente" {{ old('status', 'Pendiente') === 'Pendiente' ? 'selected' : '' }}>🟡 Pendiente</option>
                <option value="En progreso" {{ old('status') === 'En progreso' ? 'selected' : '' }}>🔵 En progreso</option>
                <option value="Completada" {{ old('status') === 'Completada' ? 'selected' : '' }}>🟢 Completada</option>
            </select>
            @error('status')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Botones de acción --}}
        <div style="display: flex; gap: 12px;">
            <button type="submit" style="width: auto; padding: 10px 24px; background: #2563eb; color: #fff; font-weight: 600; border-radius: 6px; cursor: pointer; border: 0;">
                Guardar Tarea
            </button>
            <a href="{{ route('tasks.index') }}" style="display: inline-block; padding: 10px 20px; background: #f3f4f6; color: #4b5563; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 0.95rem; border: 1px solid #d1d5db;">
                Cancelar
            </a>
        </div>
    </form>
@endsection
