<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    /**
     * Campos asignables masivamente.
     * SPRINT 3: HU-03 (CA-01)
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * Casteo de atributos.
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    /**
     * Relación con el usuario propietario de la tarea.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // MÉTODOS DE DOMINIO Y LÓGICA DE NEGOCIO (Para Pruebas Unitarias - Sprint 3)
    // =========================================================================

    /**
     * Método 1: Calcula la duración en días de la tarea (inclusiva).
     */
    public function duracionEnDias(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        if ($this->end_date->lessThan($this->start_date)) {
            return 0;
        }

        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Método 2: Evalúa si la tarea cae dentro de una fecha específica del calendario.
     */
    public function aplicaEnFecha(string|\DateTimeInterface $fecha): bool
    {
        if (!$this->start_date || !$this->end_date) {
            return false;
        }

        $target = \Carbon\Carbon::parse($fecha)->format('Y-m-d');
        $start = $this->start_date->format('Y-m-d');
        $end = $this->end_date->format('Y-m-d');

        return $target >= $start && $target <= $end;
    }

    /**
     * Método 3: Valida si la fecha final es consistente con la fecha inicial (end_date >= start_date).
     */
    public function rangoDeFechasEsValido(): bool
    {
        if (!$this->start_date || !$this->end_date) {
            return false;
        }

        return $this->end_date->greaterThanOrEqualTo($this->start_date);
    }

    /**
     * Método 4: Comprueba si la tarea se encuentra vencida respecto a una fecha dada.
     */
    public function estaVencida(?\DateTimeInterface $fechaReferencia = null): bool
    {
        if ($this->esCompletada() || !$this->end_date) {
            return false;
        }

        $ref = $fechaReferencia ? \Carbon\Carbon::parse($fechaReferencia) : \Carbon\Carbon::now();

        return $ref->format('Y-m-d') > $this->end_date->format('Y-m-d');
    }

    /**
     * Método 5: Determina si la tarea se encuentra en estado 'Completada'.
     */
    public function esCompletada(): bool
    {
        return $this->status === 'Completada';
    }

    /**
     * Método 6: Determina si la tarea se encuentra en estado 'Pendiente'.
     */
    public function esPendiente(): bool
    {
        return $this->status === 'Pendiente';
    }
}
