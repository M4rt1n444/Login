@extends('layouts.app')
@section('title', 'Detalle de la Tarea')
@section('cardClass', 'card-wide')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid #e5e7eb; padding-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h1 style="margin: 0; color: #1e3a8a;">🔍 Detalle de la Tarea</h1>
            <p style="margin: 4px 0 0; color: #6b7280; font-size: 0.9rem;">
                Información completa registrada para esta actividad.
            </p>
        </div>
        <div>
            <a href="{{ route('tasks.index') }}" style="display: inline-block; padding: 8px 14px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.85rem; border: 1px solid #d1d5db;">
                &larr; Volver al Listado y Calendario
            </a>
        </div>
    </div>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <h2 style="margin: 0; color: #0f172a; font-size: 1.4rem;">
                {{ $task->name }}
            </h2>
            <div>
                @if ($task->status === 'Pendiente')
                    <span style="background: #fef3c7; color: #92400e; padding: 6px 14px; border-radius: 16px; font-size: 0.85rem; font-weight: 700; border: 1px solid #fde68a;">
                        🟡 Pendiente
                    </span>
                @elseif ($task->status === 'En progreso')
                    <span style="background: #dbeafe; color: #1e40af; padding: 6px 14px; border-radius: 16px; font-size: 0.85rem; font-weight: 700; border: 1px solid #bfdbfe;">
                        🔵 En progreso
                    </span>
                @elseif ($task->status === 'Completada')
                    <span style="background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 16px; font-size: 0.85rem; font-weight: 700; border: 1px solid #bbf7d0;">
                        🟢 Completada
                    </span>
                @endif
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 0.9rem; text-transform: uppercase; color: #64748b; margin: 0 0 6px 0; letter-spacing: 0.5px;">Descripción</h3>
            <p style="margin: 0; color: #334155; font-size: 1rem; line-height: 1.6; background: #fff; padding: 14px; border-radius: 6px; border: 1px solid #e2e8f0;">
                {{ $task->description ?: 'No se proporcionó una descripción para esta tarea.' }}
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div style="background: #fff; padding: 12px 16px; border-radius: 6px; border: 1px solid #e2e8f0;">
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">FECHA DE INICIO</div>
                <div style="font-size: 1.1rem; color: #0f172a; font-weight: bold; margin-top: 4px;">
                    📅 {{ $task->start_date?->format('d/m/Y') }}
                </div>
            </div>

            <div style="background: #fff; padding: 12px 16px; border-radius: 6px; border: 1px solid #e2e8f0;">
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">FECHA DE FINALIZACIÓN</div>
                <div style="font-size: 1.1rem; color: #0f172a; font-weight: bold; margin-top: 4px;">
                    🏁 {{ $task->end_date?->format('d/m/Y') }}
                </div>
            </div>
        </div>

        <div style="font-size: 0.8rem; color: #94a3b8;">
            Registrada el {{ $task->created_at?->format('d/m/Y \a \l\a\s H:i') }} • Última modificación: {{ $task->updated_at?->format('d/m/Y \a \l\a\s H:i') }}
        </div>
    </div>

    {{-- Acciones --}}
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('tasks.edit', $task) }}"
           style="padding: 10px 20px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.9rem;">
            ✏️ Editar Tarea
        </a>

        <form method="POST" action="{{ route('tasks.destroy', $task) }}" style="margin: 0;"
              onsubmit="return confirm('¿Confirma que desea eliminar la tarea «{{ $task->name }}»? Esta acción no se puede deshacer.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    style="width: auto; margin: 0; padding: 10px 20px; background: #dc2626; color: #fff; border: 0; border-radius: 6px; font-weight: 600; font-size: 0.9rem; cursor: pointer;">
                🗑️ Eliminar Tarea
            </button>
        </form>

        <a href="{{ route('tasks.index') }}"
           style="padding: 10px 18px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 0.9rem; border: 1px solid #d1d5db;">
            Volver
        </a>
    </div>
@endsection
