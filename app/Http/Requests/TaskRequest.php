<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     * CA-09: Solo usuarios autenticados pueden gestionar tareas.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Reglas de validación para la creación y edición de tareas.
     * SPRINT 3: CA-01, CA-02, CA-10.
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date'  => ['required', 'date'],
            // CA-02: Rechazar fechas inconsistentes (fin anterior al inicio)
            'end_date'    => ['required', 'date', 'after_or_equal:start_date'],
            // CA-10: El estado solo acepta: Pendiente, En progreso o Completada
            'status'      => ['required', 'string', Rule::in(['Pendiente', 'En progreso', 'Completada'])],
        ];
    }

    /**
     * Mensajes descriptivos de error.
     * CA-11: Los mensajes de validación explican qué dato debe corregirse.
     */
    public function messages(): array
    {
        return [
            'name.required'             => 'El nombre de la tarea es obligatorio.',
            'name.string'               => 'El nombre de la tarea debe ser un texto válido.',
            'name.max'                  => 'El nombre de la tarea no puede superar los 255 caracteres.',
            'start_date.required'       => 'La fecha de inicio es obligatoria.',
            'start_date.date'           => 'La fecha de inicio debe tener un formato de fecha válido.',
            'end_date.required'         => 'La fecha de fin es obligatoria.',
            'end_date.date'             => 'La fecha de fin debe tener un formato de fecha válido.',
            'end_date.after_or_equal'   => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
            'status.required'           => 'El estado es obligatorio.',
            'status.in'                 => 'El estado seleccionado no es válido. Debe ser: Pendiente, En progreso o Completada.',
        ];
    }
}
