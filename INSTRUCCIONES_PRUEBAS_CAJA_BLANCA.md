# 🧪 INSTRUCCIONES PARA EL EQUIPO DE PRUEBAS — SPRINT 3
## Pruebas de Caja Blanca, Cobertura de Código y Organizador de Tareas

> **Documento elaborado para el área de pruebas (Testing / QA):**  
> Este documento contiene toda la información técnica, herramientas, comandos, mapa de decisiones del código y casos de prueba requeridos para evaluar la cobertura de líneas y ramas en el Sprint 3.

---

## 1. Contexto del Producto Desarrollado
* **Historia de Usuario (Sprint 3):**  
  > *"Como usuario autenticado, quiero crear, consultar, editar y eliminar tareas con nombre, descripción, fecha de inicio, fecha de fin y estado, para organizar mis actividades y verlas en un calendario."*
* **Resultado del desarrollo:**  
  CRUD completo de tareas (crear, listar, consultar, editar y eliminar) integrado bidireccionalmente con un calendario mensual de actividades.
* **Componentes implementados:**
  - **Migración:** `database/migrations/2026_11_22_000004_create_tasks_table.php`
  - **Modelo:** `app/Models/Task.php` (relacionado con `User::class`)
  - **Validaciones:** `app/Http/Requests/TaskRequest.php`
  - **Controlador:** `app/Http/Controllers/TaskController.php`
  - **Vistas:** `resources/views/tasks/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`
  - **Rutas:** `Route::resource('tasks', TaskController::class)` bajo middleware `auth`.

---

## 2. Herramienta de Cobertura: Xdebug en Windows / XAMPP

Para generar reportes de cobertura (`coverage-html`) en PHP / Laravel se requiere la extensión **Xdebug** en modo `coverage`.

### 2.1 Verificar si Xdebug ya está instalado
Abre tu consola de PowerShell y ejecuta:
```powershell
php -v
php -m | Select-String -Pattern "xdebug|pcov"
php --ri xdebug
```
Si aparece `Coverage => enabled` y `xdebug.mode => coverage`, ya estás listo para el paso 2.3.

### 2.2 Habilitar Xdebug en XAMPP (`php.ini`)
Si Xdebug no está activo:
1. Abre el archivo de configuración de PHP en XAMPP:  
   `C:\xampp\php\php.ini`
2. Ve al final del archivo y agrega o verifica la directiva:
   ```ini
   [Xdebug]
   zend_extension = xdebug
   xdebug.mode = coverage
   ```
   *(Nota: Si usas la DLL descargada de xdebug.org para tu versión de PHP, la ruta típica es `zend_extension = "C:\xampp\php\ext\php_xdebug.dll"`).*
3. Guarda el archivo y reinicia el servidor Apache/PHP en el Panel de XAMPP.
4. Vuelve a comprobar con:
   ```powershell
   php --ri xdebug
   ```

### 2.3 Comandos para ejecutar las pruebas con Cobertura
```powershell
# 1. Ejecutar cobertura en consola (muestra tabla con porcentajes):
php artisan test --coverage

# 2. Generar el reporte navegable en HTML (carpeta /coverage):
php artisan test --coverage-html coverage

# 3. Abrir el reporte HTML en tu navegador (en Windows):
start coverage/index.html
```

---

## 3. Matriz de Criterios de Aceptación (Trazabilidad CA-01 a CA-12)

| Criterio | Descripción | Dónde se implementa en el código | Caso de prueba en `TaskCrudTest.php` |
| :--- | :--- | :--- | :--- |
| **CA-01** | Usuario autenticado crea tarea con los 5 campos | `TaskController@store` + `TaskRequest` | `test_usuario_autenticado_puede_crear_tarea_valida` |
| **CA-02** | Valida obligatorios y rechaza `end_date < start_date` | `TaskRequest::rules` (`after_or_equal:start_date`) | `test_rechaza_creacion_con_nombre_vacio`, `test_rechaza_fecha_fin_anterior_a_fecha_inicio` |
| **CA-03** | Tarea aparece en listado y calendario | `TaskController@index` + `tasks/index.blade.php` | `test_tarea_aparece_en_listado_y_calendario` |
| **CA-04** | Consultar datos completos de una tarea | `TaskController@show` + `tasks/show.blade.php` | `test_usuario_puede_consultar_detalle_de_tarea` |
| **CA-05** | Modificar nombre, descripción, fechas y estado | `TaskController@update` + `TaskRequest` | `test_usuario_puede_editar_y_actualizar_tarea` |
| **CA-06** | Cambios guardados se reflejan en lista y calendario | `TaskController@update` + `TaskController@index` | `test_usuario_puede_editar_y_actualizar_tarea` |
| **CA-07** | Eliminar tarea previa confirmación | `TaskController@destroy` + `onsubmit="confirm(...)"` | `test_usuario_puede_eliminar_tarea` |
| **CA-08** | Tarea eliminada desaparece de lista y calendario | `TaskController@destroy` (`$task->delete()`) | `test_usuario_puede_eliminar_tarea` |
| **CA-09** | Visitante no autenticado no puede gestionar tareas | `routes/web.php` (`middleware('auth')`) | `test_visitante_no_autenticado_es_redirigido_a_login` |
| **CA-10** | Estado solo acepta: `Pendiente`, `En progreso`, `Completada` | `TaskRequest::rules` (`Rule::in(...)`) | `test_rechaza_estado_invalido` |
| **CA-11** | Mensajes de validación claros y explicativos | `TaskRequest::messages` | Evaluado en asserts de sesiones de error |
| **CA-12** | Tarea inexistente o ajena produce respuesta 404 | `TaskController` (`abort(404)`) | `test_operacion_sobre_tarea_inexistente_retorna_404`, `test_usuario_no_puede_acceder_a_tareas_de_otro_usuario` |

---

## 4. Mapa de Decisiones y Ramas (Para Pruebas de Caja Blanca)

Las pruebas de caja blanca evalúan que **ambos caminos de cada condición lógica (`true` y `false`)** se hayan ejecutado. En el desarrollo se dejaron explícitamente las siguientes decisiones:

### Decisión 1: Rango de Fechas (`end_date >= start_date`)
* **Ubicación:** `app/Http/Requests/TaskRequest.php`
* **Rama Verdadera (True):** `end_date` es mayor o igual a `start_date` ➔ Pasa validación y guarda la tarea.
* **Rama Falsa (False):** `end_date` es anterior a `start_date` ➔ Rechaza la petición con error `'end_date'`.

### Decisión 2: Validación del Estado (`status in ['Pendiente', 'En progreso', 'Completada']`)
* **Ubicación:** `app/Http/Requests/TaskRequest.php`
* **Rama Verdadera (True):** Se envía un valor permitido ➔ Pasa la validación.
* **Rama Falsa (False):** Se envía cualquier otro texto o valor nulo ➔ Rechaza con error `'status'`.

### Decisión 3: Pertenencia de la Tarea al Usuario Autenticado (Aislamiento de Seguridad)
* **Ubicación:** `app/Http/Controllers/TaskController.php` en los métodos `show()`, `edit()`, `update()`, `destroy()`.
* **Condición:** `if ($task->user_id !== auth()->id()) abort(404);`
* **Rama Verdadera (Es propietario):** Permite ver, editar o eliminar la tarea (código 200 / 302).
* **Rama Falsa (Es tarea de otro usuario):** Se ejecuta `abort(404)` bloqueando el acceso.

### Decisión 4: Filtro Opcional de Estado en el Listado
* **Ubicación:** `app/Http/Controllers/TaskController.php@index`
* **Condición:** `if (!empty($statusFilter) && in_array($statusFilter, [...]))`
* **Rama con filtro:** Aplica `$query->where('status', $statusFilter)`.
* **Rama sin filtro:** Retorna todas las tareas del usuario.

### Decisión 5: Navegación del Calendario (Manejo de Desborde de Meses)
* **Ubicación:** `app/Http/Controllers/TaskController.php@index`
* **Condición:** `if ($month < 1)` (retrocede año y fija mes 12) vs `elseif ($month > 12)` (avanza año y fija mes 1).

---

## 5. Suite Base ya Implementada y Funcionando
El archivo de pruebas inicial para el tester ya se encuentra en:
📁 **`tests/Feature/TaskCrudTest.php`**

Cuenta con **12 pruebas automatizadas y 41 aserciones** aprobadas al 100%:
```powershell
php artisan test tests/Feature/TaskCrudTest.php
```

---

## 6. Métodos de Dominio y Pruebas Unitarias (Para Documentar en Excel)

La docente / pauta del Sprint 3 solicita:  
> *"Al menos 5 métodos, por cada método una prueba unitaria. Se documentan las pruebas unitarias en Excel y después de las unitarias van las de caja blanca."*

En el modelo `app/Models/Task.php` se desarrollaron **6 métodos de dominio** con su respectiva suite unitaria en:  
📁 **`tests/Unit/TaskUnitTest.php`** (Bajo el patrón **Arrange - Act - Assert (AAA)**).

Comando para ejecutar únicamente las pruebas unitarias:
```powershell
php artisan test --testsuite=Unit
```

### Tabla Lista para Copiar / Pegar en el Excel de Entrega:

| ID Caso Unitario | Método Evaluado | Clase / Archivo | Escenario / Condición | Patrón AAA (Arrange - Act - Assert) | Resultado Esperado | Estado |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **PU-01** | `duracionEnDias()` | `App\Models\Task` | Rango válido normal (2026-11-20 a 2026-11-25) | **Arrange:** Tarea con fechas válidas.<br>**Act:** `$tarea->duracionEnDias()`<br>**Assert:** Comprobar que retorna 6 días. | Retorna entero `6` (días inclusivos). | **Aprobado** |
| **PU-02** | `duracionEnDias()` | `App\Models\Task` | Rango inválido (fecha fin menor a inicio) o fechas nulas | **Arrange:** Tarea con fin anterior a inicio.<br>**Act:** `$tarea->duracionEnDias()`<br>**Assert:** Comprobar retorno 0. | Retorna `0` sin lanzar excepciones. | **Aprobado** |
| **PU-03** | `aplicaEnFecha($fecha)` | `App\Models\Task` | Pertenencia de una fecha a la ventana de la tarea | **Arrange:** Tarea del 20 al 25 de nov.<br>**Act:** Evaluar fechas 19, 20, 22, 25 y 26 nov.<br>**Assert:** Validar booleanos true/false. | `true` si cae dentro del rango (inclusivo), `false` si es exterior. | **Aprobado** |
| **PU-04** | `rangoDeFechasEsValido()` | `App\Models\Task` | Coherencia temporal (`end_date >= start_date`) | **Arrange:** Instanciar tareas válidas e inconsistentes.<br>**Act:** `$tarea->rangoDeFechasEsValido()`<br>**Assert:** Validar coherencia lógica. | `true` si `end >= start`; `false` en caso contrario. | **Aprobado** |
| **PU-05** | `estaVencida($ref)` | `App\Models\Task` | Detección de vencimiento con respecto a una fecha | **Arrange:** Tarea atrasada pendiente vs completada.<br>**Act:** `$tarea->estaVencida($hoy)`<br>**Assert:** Verificar estado de vencimiento. | `true` si fecha fin expiró y NO está completada. | **Aprobado** |
| **PU-06** | `esCompletada()` y `esPendiente()` | `App\Models\Task` | Evaluación de estados del ciclo de vida | **Arrange:** Tareas en estado Pendiente, En progreso, Completada.<br>**Act:** `$tarea->esCompletada()`, `$tarea->esPendiente()`<br>**Assert:** Comprobar flags de estado. | Booleanos acordes al estado actual. | **Aprobado** |

---

## 7. Resumen de Ejecución General del Proyecto
Al ejecutar toda la suite de pruebas del sistema:
```powershell
php artisan test
```
**Resultado:** **60 tests passed, 260 assertions (0 errores).**  
Todo el desarrollo del Sprint 3 está funcional, aislado y listo para sustentar y documentar cobertura.

