<?php

namespace Tests\Unit;

use App\Models\Task;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Pruebas Unitarias para el Modelo Task (Sprint 3 - Métodos de Dominio).
 * Implementa el patrón Arrange - Act - Assert (AAA).
 * Diseñadas para documentar en la matriz de pruebas (Excel) y evaluar decisiones (Caja Blanca).
 * 
 * Se ejecutan en consola mediante: php artisan test --testsuite=Unit
 */
class TaskUnitTest extends TestCase
{
    /**
     * MÉTODO 1: duracionEnDias()
     * Escenario 1.1: Tarea con rango de fechas válido calcula la duración inclusiva en días.
     */
    public function test_metodo1_duracion_en_dias_calcula_dias_inclusivos(): void
    {
        // 1. Arrange (Preparar el objeto Task con fechas de 5 días de diferencia = 6 días inclusivos)
        $tarea = new Task([
            'name'       => 'Estudiar para examen',
            'start_date' => Carbon::parse('2026-11-20'),
            'end_date'   => Carbon::parse('2026-11-25'),
            'status'     => 'Pendiente',
        ]);

        // 2. Act (Ejecutar el método de cálculo de duración)
        $duracion = $tarea->duracionEnDias();

        // 3. Assert (Comprobar que el resultado son 6 días)
        $this->assertSame(6, $duracion, 'La duración entre 2026-11-20 y 2026-11-25 inclusiva debe ser de 6 días.');
    }

    /**
     * MÉTODO 1: duracionEnDias()
     * Escenario 1.2: Si la fecha fin es anterior a la fecha inicio o faltan fechas, retorna 0.
     */
    public function test_metodo1_duracion_en_dias_retorna_cero_con_fechas_invalidas(): void
    {
        // 1. Arrange (Tarea con fecha fin menor que fecha inicio)
        $tareaInvalida = new Task([
            'start_date' => Carbon::parse('2026-11-25'),
            'end_date'   => Carbon::parse('2026-11-20'),
        ]);
        $tareaVacia = new Task();

        // 2. Act (Ejecutar método en ambos casos)
        $resultadoInvalida = $tareaInvalida->duracionEnDias();
        $resultadoVacia = $tareaVacia->duracionEnDias();

        // 3. Assert (Debe devolver 0 en ambos)
        $this->assertSame(0, $resultadoInvalida, 'Debe retornar 0 si fecha fin es menor que fecha inicio.');
        $this->assertSame(0, $resultadoVacia, 'Debe retornar 0 si faltan fechas.');
    }

    /**
     * MÉTODO 2: aplicaEnFecha()
     * Escenario 2.1: Evalúa si una fecha específica cae dentro del rango de la tarea.
     */
    public function test_metodo2_aplica_en_fecha_identifica_si_cae_en_rango(): void
    {
        // 1. Arrange (Tarea programada del 20 al 25 de noviembre)
        $tarea = new Task([
            'start_date' => Carbon::parse('2026-11-20'),
            'end_date'   => Carbon::parse('2026-11-25'),
        ]);

        // 2. Act (Evaluar límites y días intermedios/exteriores)
        $diaAntes = $tarea->aplicaEnFecha('2026-11-19');
        $diaInicio = $tarea->aplicaEnFecha('2026-11-20');
        $diaMedio = $tarea->aplicaEnFecha('2026-11-22');
        $diaFin = $tarea->aplicaEnFecha('2026-11-25');
        $diaDespues = $tarea->aplicaEnFecha('2026-11-26');

        // 3. Assert
        $this->assertFalse($diaAntes, '19 de noviembre debe ser falso (antes del inicio).');
        $this->assertTrue($diaInicio, '20 de noviembre debe ser verdadero (día de inicio).');
        $this->assertTrue($diaMedio, '22 de noviembre debe ser verdadero (en el rango).');
        $this->assertTrue($diaFin, '25 de noviembre debe ser verdadero (día de fin).');
        $this->assertFalse($diaDespues, '26 de noviembre debe ser falso (después del fin).');
    }

    /**
     * MÉTODO 3: rangoDeFechasEsValido()
     * Escenario 3.1: Valida coherencia lógica de end_date >= start_date.
     */
    public function test_metodo3_rango_de_fechas_es_valido_evalua_coherencia(): void
    {
        // 1. Arrange (Dos tareas: una válida y otra inconsistente)
        $tareaValida = new Task([
            'start_date' => Carbon::parse('2026-12-01'),
            'end_date'   => Carbon::parse('2026-12-01'), // Mismo día es válido
        ]);
        $tareaInvalida = new Task([
            'start_date' => Carbon::parse('2026-12-05'),
            'end_date'   => Carbon::parse('2026-12-02'), // Fin antes que inicio
        ]);

        // 2. Act
        $esValida = $tareaValida->rangoDeFechasEsValido();
        $esInvalida = $tareaInvalida->rangoDeFechasEsValido();

        // 3. Assert
        $this->assertTrue($esValida, 'Mismo día de inicio y fin debe ser válido.');
        $this->assertFalse($esInvalida, 'Fin menor a inicio debe retornar falso.');
    }

    /**
     * MÉTODO 4: estaVencida()
     * Escenario 4.1: Detecta si una tarea no completada supera la fecha de vencimiento.
     */
    public function test_metodo4_esta_vencida_detecta_tarea_atrasada_no_completada(): void
    {
        // 1. Arrange (Fecha de referencia: 22 de noviembre de 2026)
        $hoy = Carbon::parse('2026-11-22');

        // Tarea que venció ayer y sigue Pendiente
        $tareaAtrasada = new Task([
            'end_date' => Carbon::parse('2026-11-21'),
            'status'   => 'Pendiente',
        ]);

        // Tarea con fecha de ayer pero ya Completada
        $tareaCompletada = new Task([
            'end_date' => Carbon::parse('2026-11-21'),
            'status'   => 'Completada',
        ]);

        // Tarea con fecha futura y Pendiente
        $tareaFutura = new Task([
            'end_date' => Carbon::parse('2026-11-30'),
            'status'   => 'Pendiente',
        ]);

        // 2. Act
        $vencidaAtrasada = $tareaAtrasada->estaVencida($hoy);
        $vencidaCompletada = $tareaCompletada->estaVencida($hoy);
        $vencidaFutura = $tareaFutura->estaVencida($hoy);

        // 3. Assert
        $this->assertTrue($vencidaAtrasada, 'Una tarea pendiente cuya fecha fin ya pasó debe reportarse vencida.');
        $this->assertFalse($vencidaCompletada, 'Una tarea completada nunca debe reportarse vencida.');
        $this->assertFalse($vencidaFutura, 'Una tarea con fecha futura no está vencida.');
    }

    /**
     * MÉTODOS 5 y 6: esCompletada() y esPendiente()
     * Escenario 5.1: Comprueba los estados del ciclo de vida de la tarea.
     */
    public function test_metodos5_y_6_evaluan_estados_del_ciclo_de_vida(): void
    {
        // 1. Arrange (Tareas con diferentes estados)
        $tareaPendiente = new Task(['status' => 'Pendiente']);
        $tareaEnProgreso = new Task(['status' => 'En progreso']);
        $tareaCompletada = new Task(['status' => 'Completada']);

        // 2. Act & 3. Assert
        // Evaluar esCompletada()
        $this->assertFalse($tareaPendiente->esCompletada(), 'Pendiente no es completada.');
        $this->assertFalse($tareaEnProgreso->esCompletada(), 'En progreso no es completada.');
        $this->assertTrue($tareaCompletada->esCompletada(), 'Completada debe ser verdadero.');

        // Evaluar esPendiente()
        $this->assertTrue($tareaPendiente->esPendiente(), 'Pendiente debe retornar verdadero.');
        $this->assertFalse($tareaEnProgreso->esPendiente(), 'En progreso no es pendiente.');
        $this->assertFalse($tareaCompletada->esPendiente(), 'Completada no es pendiente.');
    }
}
