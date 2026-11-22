@extends('layouts.app')
@section('title', 'Organizador de Tareas y Calendario')
@section('cardClass', 'card-wide')

@section('content')
    {{-- Barra superior de navegación y usuario --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; border-bottom: 2px solid #e5e7eb; padding-bottom: 16px;">
        <div>
            <h1 style="margin: 0; color: #1e3a8a; font-size: 1.6rem;">📅 Organizador de Tareas</h1>
            <p style="margin: 4px 0 0; color: #6b7280; font-size: 0.95rem;">
                Usuario: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('dashboard') }}" style="display: inline-block; padding: 8px 14px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.85rem; border: 1px solid #d1d5db;">
                👥 Ver Usuarios
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="width: auto; margin-top: 0; background: #dc2626; color: #fff; padding: 8px 16px; border-radius: 6px; font-weight: 500; font-size: 0.85rem; cursor: pointer; border: 0;">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>

    {{-- Mensaje flash de confirmación --}}
    @if (session('status'))
        <div class="alert-ok" style="margin-bottom: 18px; font-weight: 500;">
            ✓ {{ session('status') }}
        </div>
    @endif

    {{-- Botón de acción crear y filtros --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; background: #f9fafb; padding: 14px 18px; border-radius: 8px; border: 1px solid #e5e7eb;">
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <span style="font-weight: 600; font-size: 0.9rem; color: #374151;">Filtrar por estado:</span>
            <a href="{{ route('tasks.index', ['year' => $year, 'month' => $month]) }}"
               style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; text-decoration: none; {{ empty($statusFilter) ? 'background:#2563eb; color:#fff;' : 'background:#e5e7eb; color:#374151;' }}">
               Todas ({{ $tasks->count() }})
            </a>
            <a href="{{ route('tasks.index', ['status' => 'Pendiente', 'year' => $year, 'month' => $month]) }}"
               style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; text-decoration: none; {{ $statusFilter === 'Pendiente' ? 'background:#d97706; color:#fff;' : 'background:#fef3c7; color:#92400e;' }}">
               Pendiente
            </a>
            <a href="{{ route('tasks.index', ['status' => 'En progreso', 'year' => $year, 'month' => $month]) }}"
               style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; text-decoration: none; {{ $statusFilter === 'En progreso' ? 'background:#2563eb; color:#fff;' : 'background:#dbeafe; color:#1e40af;' }}">
               En progreso
            </a>
            <a href="{{ route('tasks.index', ['status' => 'Completada', 'year' => $year, 'month' => $month]) }}"
               style="padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; text-decoration: none; {{ $statusFilter === 'Completada' ? 'background:#16a34a; color:#fff;' : 'background:#dcfce7; color:#166534;' }}">
               Completada
            </a>
        </div>
        <div>
            <a href="{{ route('tasks.create') }}" style="display: inline-block; padding: 9px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.9rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                ➕ Crear Nueva Tarea
            </a>
        </div>
    </div>

    {{-- ================================================================= --}}
    {{-- SECCIÓN 1: VISTA DE LISTADO (CRUD)                               --}}
    {{-- ================================================================= --}}
    <div style="margin-bottom: 36px;">
        <h2 style="color: #1e3a8a; font-size: 1.25rem; margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <span>📋 Listado de Tareas</span>
            <span style="font-size: 0.85rem; font-weight: normal; color: #6b7280;">({{ $tasks->count() }} registradas)</span>
        </h2>

        @if ($tasks->isEmpty())
            <div style="background: #fff; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 32px; text-align: center; color: #64748b;">
                <p style="margin: 0 0 12px; font-size: 1.05rem;">No tienes tareas registradas con este filtro.</p>
                <a href="{{ route('tasks.create') }}" style="color: #2563eb; font-weight: 600;">Haz clic aquí para crear tu primera tarea</a>
            </div>
        @else
            <div class="table-container" style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #fff;">
                <table class="users-table" style="margin: 0;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 12px 14px;">Nombre</th>
                            <th style="padding: 12px 14px;">Descripción</th>
                            <th style="padding: 12px 14px;">Fecha Inicio</th>
                            <th style="padding: 12px 14px;">Fecha Fin</th>
                            <th style="padding: 12px 14px; text-align: center;">Estado</th>
                            <th style="padding: 12px 14px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr>
                                <td style="font-weight: 600; color: #0f172a; padding: 12px 14px;">
                                    {{ $task->name }}
                                </td>
                                <td style="color: #475569; padding: 12px 14px; max-width: 250px;">
                                    {{ Str::limit($task->description ?? 'Sin descripción', 60) }}
                                </td>
                                <td style="color: #334155; padding: 12px 14px; white-space: nowrap;">
                                    📅 {{ $task->start_date?->format('d/m/Y') }}
                                </td>
                                <td style="color: #334155; padding: 12px 14px; white-space: nowrap;">
                                    🏁 {{ $task->end_date?->format('d/m/Y') }}
                                </td>
                                <td style="text-align: center; padding: 12px 14px;">
                                    @if ($task->status === 'Pendiente')
                                        <span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                            Pendiente
                                        </span>
                                    @elseif ($task->status === 'En progreso')
                                        <span style="background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                            En progreso
                                        </span>
                                    @elseif ($task->status === 'Completada')
                                        <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                            Completada
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: center; padding: 12px 14px; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        {{-- CA-04: Consultar datos completos --}}
                                        <a href="{{ route('tasks.show', $task) }}"
                                           style="padding: 5px 10px; background: #e0f2fe; color: #0369a1; text-decoration: none; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                            Ver
                                        </a>

                                        {{-- CA-05: Modificar tarea --}}
                                        <a href="{{ route('tasks.edit', $task) }}"
                                           style="padding: 5px 10px; background: #fef08a; color: #854d0e; text-decoration: none; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                            Editar
                                        </a>

                                        {{-- CA-07: Eliminar tarea con confirmación previa --}}
                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" style="display: inline; margin: 0;"
                                              onsubmit="return confirm('¿Confirma que desea eliminar la tarea «{{ $task->name }}»? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="width: auto; margin: 0; padding: 5px 10px; background: #fee2e2; color: #b91c1c; border: 0; border-radius: 4px; font-size: 0.8rem; font-weight: 600; cursor: pointer;">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ================================================================= --}}
    {{-- SECCIÓN 2: VISTA DE CALENDARIO (CA-03, CA-06, CA-08)             --}}
    {{-- ================================================================= --}}
    <div style="border-top: 2px solid #e5e7eb; padding-top: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <h2 style="color: #1e3a8a; font-size: 1.25rem; margin: 0;">
                📅 Calendario de Actividades: {{ $calendarDate->locale('es')->isoFormat('MMMM YYYY') }}
            </h2>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('tasks.index', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1, 'status' => $statusFilter]) }}"
                   style="padding: 6px 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #1e293b; font-size: 0.85rem; font-weight: 600;">
                    &laquo; Mes anterior
                </a>
                <a href="{{ route('tasks.index', ['year' => now()->year, 'month' => now()->month, 'status' => $statusFilter]) }}"
                   style="padding: 6px 14px; background: #2563eb; color: #fff; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                    Hoy
                </a>
                <a href="{{ route('tasks.index', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1, 'status' => $statusFilter]) }}"
                   style="padding: 6px 14px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #1e293b; font-size: 0.85rem; font-weight: 600;">
                    Mes siguiente &raquo;
                </a>
            </div>
        </div>

        {{-- Cuadrícula del calendario --}}
        <div style="border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #fff;">
            {{-- Encabezado de días de la semana --}}
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); background: #1e3a8a; color: #fff; text-align: center; font-weight: 600; font-size: 0.85rem; padding: 10px 0;">
                <div>Lunes</div>
                <div>Martes</div>
                <div>Miércoles</div>
                <div>Jueves</div>
                <div>Viernes</div>
                <div>Sábado</div>
                <div>Domingo</div>
            </div>

            {{-- Celdas de los días --}}
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); background: #e2e8f0; gap: 1px;">
                {{-- Celdas vacías antes del primer día del mes --}}
                @for ($i = 1; $i < $startDayOfWeek; $i++)
                    <div style="background: #f8fafc; min-height: 90px; padding: 6px; opacity: 0.4;"></div>
                @endfor

                {{-- Días del mes --}}
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dayInfo = $calendarDays[$day];
                        $isToday = ($year == now()->year && $month == now()->month && $day == now()->day);
                    @endphp
                    <div style="background: {{ $isToday ? '#eff6ff' : '#fff' }}; min-height: 90px; padding: 6px; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: {{ $isToday ? '#1d4ed8' : '#334155' }}; {{ $isToday ? 'background:#bfdbfe; border-radius:50%; width:20px; height:20px; display:inline-flex; align-items:center; justify-content:center;' : '' }}">
                                {{ $day }}
                            </span>
                            @if ($dayInfo['tasks']->isNotEmpty())
                                <span style="font-size: 0.7rem; color: #64748b; font-weight: 500;">
                                    {{ $dayInfo['tasks']->count() }} {{ $dayInfo['tasks']->count() == 1 ? 'tarea' : 'tareas' }}
                                </span>
                            @endif
                        </div>

                        {{-- Tareas que caen en este día --}}
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            @foreach ($dayInfo['tasks'] as $t)
                                @php
                                    $bgColor = '#fef3c7';
                                    $textColor = '#92400e';
                                    if ($t->status === 'En progreso') {
                                        $bgColor = '#dbeafe';
                                        $textColor = '#1e40af';
                                    } elseif ($t->status === 'Completada') {
                                        $bgColor = '#dcfce7';
                                        $textColor = '#166534';
                                    }
                                @endphp
                                <a href="{{ route('tasks.show', $t) }}"
                                   title="{{ $t->name }} ({{ $t->status }}) | {{ $t->start_date?->format('d/m') }} - {{ $t->end_date?->format('d/m') }}"
                                   style="display: block; background: {{ $bgColor }}; color: {{ $textColor }}; text-decoration: none; padding: 2px 5px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; border-left: 3px solid {{ $textColor }};">
                                    {{ $t->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endfor

                {{-- Celdas vacías para completar la última semana si es necesario --}}
                @php
                    $totalRenderedCells = ($startDayOfWeek - 1) + $daysInMonth;
                    $remainingCells = (7 - ($totalRenderedCells % 7)) % 7;
                @endphp
                @for ($j = 0; $j < $remainingCells; $j++)
                    <div style="background: #f8fafc; min-height: 90px; padding: 6px; opacity: 0.4;"></div>
                @endfor
            </div>
        </div>
    </div>
@endsection
