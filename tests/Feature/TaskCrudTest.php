<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Suite de Pruebas de Integración y Feature para SPRINT 3:
 * HU-03 / HU-Tareas: Organizador de Tareas y Calendario (CA-01 a CA-12).
 * Cubre pruebas positivas, pruebas negativas y decisiones de código (Caja Blanca).
 */
class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    private function datosTareaValidos(array $override = []): array
    {
        return array_merge([
            'name'        => 'Entrega Sprint 3',
            'description' => 'Pruebas de caja blanca y cobertura con Xdebug',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-25',
            'status'      => 'Pendiente',
        ], $override);
    }

    // =========================================================================
    // PRUEBAS POSITIVAS (HAPPY PATH)
    // =========================================================================

    /**
     * CA-01: Usuario autenticado puede crear una tarea con los 5 campos definidos.
     */
    public function test_usuario_autenticado_puede_crear_tarea_valida(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $datos = $this->datosTareaValidos();

        // 2. Act
        $response = $this->actingAs($user)->post(route('tasks.store'), $datos);

        // 3. Assert
        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'name'    => 'Entrega Sprint 3',
            'status'  => 'Pendiente',
        ]);
    }

    /**
     * CA-03: Una tarea guardada aparece en el listado y en el calendario en su rango.
     */
    public function test_tarea_aparece_en_listado_y_calendario(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $task = Task::create([
            'user_id'     => $user->id,
            'name'        => 'Reunión de Equipo',
            'description' => 'Planificación',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-24',
            'status'      => 'En progreso',
        ]);

        // 2. Act
        $response = $this->actingAs($user)->get(route('tasks.index', ['year' => 2026, 'month' => 11]));

        // 3. Assert
        $response->assertOk()
            ->assertSee('Reunión de Equipo')
            ->assertSee('En progreso');
    }

    /**
     * CA-04: El usuario puede consultar los datos completos de una tarea (show).
     */
    public function test_usuario_puede_consultar_detalle_de_tarea(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $task = Task::create([
            'user_id'     => $user->id,
            'name'        => 'Revisión de Arquitectura',
            'description' => 'Detalle técnico minucioso',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-23',
            'status'      => 'Pendiente',
        ]);

        // 2. Act
        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        // 3. Assert
        $response->assertOk()
            ->assertSee('Revisión de Arquitectura')
            ->assertSee('Detalle técnico minucioso');
    }

    /**
     * CA-05 y CA-06: Modificar datos y estado de una tarea existente.
     */
    public function test_usuario_puede_editar_y_actualizar_tarea(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $task = Task::create([
            'user_id'     => $user->id,
            'name'        => 'Tarea Inicial',
            'description' => 'Texto viejo',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-22',
            'status'      => 'Pendiente',
        ]);

        $datosActualizados = [
            'name'        => 'Tarea Modificada',
            'description' => 'Texto nuevo y actualizado',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-28',
            'status'      => 'Completada',
        ];

        // 2. Act
        $response = $this->actingAs($user)->put(route('tasks.update', $task), $datosActualizados);

        // 3. Assert
        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('tasks', [
            'id'     => $task->id,
            'name'   => 'Tarea Modificada',
            'status' => 'Completada',
        ]);
    }

    /**
     * CA-07 y CA-08: Eliminar una tarea.
     */
    public function test_usuario_puede_eliminar_tarea(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $task = Task::create([
            'user_id'     => $user->id,
            'name'        => 'Tarea para Borrar',
            'description' => 'Se eliminará',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-22',
            'status'      => 'Pendiente',
        ]);

        // 2. Act
        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));

        // 3. Assert
        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Prueba de rama: Filtro de tareas por estado en el listado.
     */
    public function test_filtro_por_estado_funciona_correctamente(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        Task::create([
            'user_id'     => $user->id,
            'name'        => 'Tarea Pendiente A',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-22',
            'status'      => 'Pendiente',
        ]);
        Task::create([
            'user_id'     => $user->id,
            'name'        => 'Tarea Completada B',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-22',
            'status'      => 'Completada',
        ]);

        // 2. Act: filtrar solo Completadas
        $response = $this->actingAs($user)->get(route('tasks.index', ['status' => 'Completada']));

        // 3. Assert
        $response->assertOk()
            ->assertSee('Tarea Completada B')
            ->assertDontSee('Tarea Pendiente A');
    }

    // =========================================================================
    // PRUEBAS NEGATIVAS (BRANCHES / VALIDACIONES)
    // =========================================================================

    /**
     * CA-02: Nombre vacío es rechazado.
     */
    public function test_rechaza_creacion_con_nombre_vacio(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $datos = $this->datosTareaValidos(['name' => '']);

        // 2. Act
        $response = $this->actingAs($user)->post(route('tasks.store'), $datos);

        // 3. Assert
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('tasks', 0);
    }

    /**
     * CA-02: Rechaza fechas inconsistentes (fecha fin anterior a fecha inicio).
     */
    public function test_rechaza_fecha_fin_anterior_a_fecha_inicio(): void
    {
        // 1. Arrange: inicio el día 25 y fin el día 20 (inconsistente)
        $user = User::factory()->create();
        $datos = $this->datosTareaValidos([
            'start_date' => '2026-11-25',
            'end_date'   => '2026-11-20',
        ]);

        // 2. Act
        $response = $this->actingAs($user)->post(route('tasks.store'), $datos);

        // 3. Assert
        $response->assertSessionHasErrors('end_date');
        $this->assertDatabaseCount('tasks', 0);
    }

    /**
     * CA-10: Rechaza estados inválidos que no sean Pendiente, En progreso o Completada.
     */
    public function test_rechaza_estado_invalido(): void
    {
        // 1. Arrange
        $user = User::factory()->create();
        $datos = $this->datosTareaValidos(['status' => 'EstadoInexistente']);

        // 2. Act
        $response = $this->actingAs($user)->post(route('tasks.store'), $datos);

        // 3. Assert
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseCount('tasks', 0);
    }

    /**
     * CA-12: Operaciones sobre tarea inexistente producen error 404 controlado.
     */
    public function test_operacion_sobre_tarea_inexistente_retorna_404(): void
    {
        // 1. Arrange
        $user = User::factory()->create();

        // 2. Act
        $response = $this->actingAs($user)->get('/tasks/99999');

        // 3. Assert
        $response->assertNotFound();
    }

    /**
     * CA-12 / Seguridad: Usuario no puede ver ni modificar tareas de otro usuario (404).
     */
    public function test_usuario_no_puede_acceder_a_tareas_de_otro_usuario(): void
    {
        // 1. Arrange
        $propietario = User::factory()->create(['email' => 'propietario@test.local']);
        $otroUsuario = User::factory()->create(['email' => 'otro@test.local']);

        $tareaAjena = Task::create([
            'user_id'     => $propietario->id,
            'name'        => 'Tarea Confidencial',
            'start_date'  => '2026-11-22',
            'end_date'    => '2026-11-22',
            'status'      => 'Pendiente',
        ]);

        // 2. Act: otroUsuario intenta consultar y editar la tarea ajena
        $respShow = $this->actingAs($otroUsuario)->get(route('tasks.show', $tareaAjena));
        $respEdit = $this->actingAs($otroUsuario)->get(route('tasks.edit', $tareaAjena));
        $respDelete = $this->actingAs($otroUsuario)->delete(route('tasks.destroy', $tareaAjena));

        // 3. Assert: Todas responden 404 Not Found
        $respShow->assertNotFound();
        $respEdit->assertNotFound();
        $respDelete->assertNotFound();

        // La tarea sigue existiendo intacta
        $this->assertDatabaseHas('tasks', ['id' => $tareaAjena->id]);
    }

    /**
     * CA-09: Un visitante no autenticado (guest) no puede gestionar tareas.
     */
    public function test_visitante_no_autenticado_es_redirigido_a_login(): void
    {
        // 1. Arrange: sin actingAs

        // 2. Act
        $respIndex = $this->get(route('tasks.index'));
        $respCreate = $this->get(route('tasks.create'));
        $respStore = $this->post(route('tasks.store'), $this->datosTareaValidos());

        // 3. Assert: todas redirigen al login
        $respIndex->assertRedirect(route('login'));
        $respCreate->assertRedirect(route('login'));
        $respStore->assertRedirect(route('login'));
    }
}
