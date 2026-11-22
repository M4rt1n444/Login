<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Muestra la lista de tareas del usuario y la vista del calendario.
     * SPRINT 3: HU-03 (CA-03, CA-06, CA-08)
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Filtro opcional por estado para pruebas de caja blanca / filtrado
        $statusFilter = $request->query('status');
        $query = $user->tasks()->orderBy('start_date', 'asc');

        if (!empty($statusFilter) && in_array($statusFilter, ['Pendiente', 'En progreso', 'Completada'])) {
            $query->where('status', $statusFilter);
        }

        $tasks = $query->get();

        // Parámetros de navegación del calendario (año y mes)
        $year = (int) $request->query('year', Carbon::now()->year);
        $month = (int) $request->query('month', Carbon::now()->month);

        if ($month < 1) {
            $month = 12;
            $year--;
        } elseif ($month > 12) {
            $month = 1;
            $year++;
        }

        $calendarDate = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $calendarDate->daysInMonth;
        // 1 (Lunes) a 7 (Domingo)
        $startDayOfWeek = $calendarDate->copy()->startOfMonth()->dayOfWeekIso;

        // Mapear qué tareas caen en cada día del mes
        $calendarDays = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDateStr = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
            $dayTasks = $tasks->filter(function ($t) use ($currentDateStr) {
                return $t->aplicaEnFecha($currentDateStr);
            });

            $calendarDays[$day] = [
                'date' => $currentDateStr,
                'tasks' => $dayTasks,
            ];
        }

        return view('tasks.index', compact(
            'tasks',
            'year',
            'month',
            'calendarDate',
            'daysInMonth',
            'startDayOfWeek',
            'calendarDays',
            'statusFilter'
        ));
    }

    /**
     * Formulario para crear una nueva tarea.
     */
    public function create(): View
    {
        return view('tasks.create');
    }

    /**
     * Guarda una nueva tarea asociada al usuario autenticado.
     * SPRINT 3: CA-01, CA-02, CA-10, CA-11
     */
    public function store(TaskRequest $request): RedirectResponse
    {
        $request->user()->tasks()->create($request->validated());

        return redirect()->route('tasks.index')
            ->with('status', 'Tarea creada exitosamente y registrada en el calendario.');
    }

    /**
     * Muestra el detalle completo de una tarea.
     * SPRINT 3: CA-04, CA-12 (Respuesta controlada 404 si no pertenece al usuario o no existe)
     */
    public function show(Task $task): View
    {
        if ($task->user_id !== auth()->id()) {
            abort(404, 'Tarea no encontrada o no autorizada.');
        }

        return view('tasks.show', compact('task'));
    }

    /**
     * Formulario de edición de una tarea existente.
     * SPRINT 3: CA-05, CA-12
     */
    public function edit(Task $task): View
    {
        if ($task->user_id !== auth()->id()) {
            abort(404, 'Tarea no encontrada o no autorizada.');
        }

        return view('tasks.edit', compact('task'));
    }

    /**
     * Actualiza los datos de una tarea existente.
     * SPRINT 3: CA-05, CA-06, CA-12
     */
    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        if ($task->user_id !== auth()->id()) {
            abort(404, 'Tarea no encontrada o no autorizada.');
        }

        $task->update($request->validated());

        return redirect()->route('tasks.index')
            ->with('status', 'Tarea actualizada exitosamente.');
    }

    /**
     * Elimina una tarea existente previa confirmación.
     * SPRINT 3: CA-07, CA-08, CA-12
     */
    public function destroy(Task $task): RedirectResponse
    {
        if ($task->user_id !== auth()->id()) {
            abort(404, 'Tarea no encontrada o no autorizada.');
        }

        $task->delete();

        return redirect()->route('tasks.index')
            ->with('status', 'Tarea eliminada exitosamente.');
    }
}
