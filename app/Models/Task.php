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
}
