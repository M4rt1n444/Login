# 📋 PROJECT_STATUS — Estado del Proyecto y Bitácora

> [!CAUTION]
> **⚠️ REGLA DE ORO PARA COLABORADORES E IA:**
> Antes de realizar cualquier cambio en el proyecto, **lee este archivo**.
> Al finalizar una tarea o Sprint, **DEBES** actualizar este archivo agregando el nuevo estado, decisiones tomadas o cambios realizados en la sección correspondiente.
> **NUNCA borres el historial previo**; únicamente actualiza, complementa o anexa la información.

---

## 1. Estado Actual del Proyecto

| Campo               | Valor                                                    |
| ------------------- | -------------------------------------------------------- |
| **Framework**       | Laravel 11.57.0                                          |
| **PHP**             | 8.2.12 (XAMPP)                                           |
| **Base de Datos**   | MySQL 8 — esquema `auth_db`                              |
| **Sprint Actual**   | Sprint 1 — Autenticación (Registro e Inicio de Sesión)   |
| **Entorno Local**   | Windows / XAMPP / Composer 2.10.3                        |

### Arquitectura

Proyecto migrado y estructurado bajo el **estándar oficial de Laravel 11**.
Toda la autenticación está implementada a mano (sin Breeze ni Jetstream) para que cada criterio de aceptación sea trazable de forma directa.

### Base de Datos

- Motor: **MySQL** (`auth_db`) configurada y probada localmente.
- El script DDL completo (creación de tablas, datos de prueba y consultas de referencia) se encuentra en [`DataBase.txt`](DataBase.txt).
- Tablas existentes: `users`, `password_resets`, `login_attempts`, `sessions`.
- La tabla `users` cuenta con las columnas `password` (convención Laravel) y `password_hash` (definición original del equipo de BD), sincronizadas automáticamente por el modelo `User`.

### Historias de Usuario Cubiertas

| HU   | Nombre               | Estado        | Notas                                                                           |
| ---- | -------------------- | ------------- | ------------------------------------------------------------------------------- |
| HU-01 | Registro de usuario | ✅ Completada | Controlador, validaciones (nombre, correo, contraseña segura, confirmación) y vista funcionales. |
| HU-02 | Inicio de sesión    | ✅ Completada | Autenticación con `Auth::attempt`, redirección al dashboard, control de sesión, mensaje genérico ante credenciales incorrectas. |
| HU-03 | Recuperar contraseña | ⬜ Pendiente  | Tabla `password_resets` ya existe en BD.                                        |
| HU-04 | Cerrar sesión        | ⬜ Pendiente  | Tabla `sessions` ya existe en BD.                                               |
| HU-05 | Límite de intentos   | ⬜ Pendiente  | Tabla `login_attempts` ya existe en BD.                                         |

### Pruebas Automatizadas

- **16 pruebas** ejecutadas y pasadas con éxito (`php artisan test`).
- Los tests cubren los casos de prueba **CP-01 a CP-14** del Plan de Pruebas (TP-S01).
- Las pruebas usan **SQLite en memoria** (configurado en `phpunit.xml`).

### Archivos Clave y Rutas

| Categoría       | Ruta                                                      | Archivos                                             |
| --------------- | --------------------------------------------------------- | ---------------------------------------------------- |
| Controladores   | `app/Http/Controllers/`                                   | `LoginController.php`, `RegisterController.php`      |
| Requests         | `app/Http/Requests/`                                      | `LoginRequest.php`, `RegisterRequest.php`            |
| Modelo           | `app/Models/`                                             | `User.php`                                           |
| Vistas           | `resources/views/`                                        | `login.blade.php`, `register.blade.php`, `dashboard.blade.php` |
| Layout           | `resources/views/layouts/`                                | `app.blade.php`                                      |
| Vistas (alias)   | `resources/views/auth/`                                   | `login.blade.php`, `register.blade.php`              |
| Rutas            | `routes/`                                                 | `web.php`                                            |
| Tests            | `tests/Feature/`                                          | `AuthenticationTest.php`                             |
| Script BD        | raíz                                                      | `DataBase.txt`                                       |
| Config local     | raíz                                                      | `.env`, `.env.example`                               |
| Bootstrap        | `bootstrap/`                                              | `app.php` (middleware `redirectUsersTo('/dashboard')`) |

### Trazabilidad de Criterios de Aceptación

| Criterio            | Dónde se cumple                                                                |
| ------------------- | ------------------------------------------------------------------------------ |
| CA-01.1 / CA-01.2   | `register.blade.php` + `RegisterRequest` (campos required)                     |
| CA-01.3             | `RegisterRequest` → `prepareForValidation` (email a minúsculas) + `unique`     |
| CA-01.4             | `RegisterRequest` (min:8, regex mayúscula, minúscula, número)                  |
| CA-01.5             | `RegisterRequest` (confirmed)                                                  |
| CA-01.6             | `RegisterController@store` (flash `status`)                                    |
| CA-01.7             | Cast `hashed` en modelo `User` (bcrypt automático)                             |
| CA-02.1 / CA-02.4   | `login.blade.php` + `LoginRequest`                                             |
| CA-02.2 / CA-02.3   | `LoginController@store` (`Auth::attempt`, error genérico)                      |
| CA-02.5             | `redirect()->intended(route('dashboard'))`                                     |
| CA-02.6             | `Route::middleware('auth')` en `routes/web.php`                                |

---

## 2. Bitácora de Cambios y Progreso (Changelog)

### 2026-10-25 — Restructuración a Laravel 11 y verificación de HU-01 / HU-02

**Responsable:** Samuel (Tester / Integrador)

**Cambios realizados:**

1. **Estructura del proyecto migrada al estándar oficial de Laravel 11:**
   - Se generó la estructura completa de carpetas (`app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `tests/`).
   - Se instalaron todas las dependencias del framework mediante `composer install`.
   - Se generó una clave de aplicación válida (`APP_KEY`) con `php artisan key:generate`.

2. **Archivos del equipo reubicados a sus carpetas estándar:**
   - Controladores → `app/Http/Controllers/`
   - Form Requests → `app/Http/Requests/`
   - Vistas Blade → `resources/views/`, `resources/views/layouts/`, `resources/views/auth/`
   - Rutas → `routes/web.php`
   - Tests → `tests/Feature/AuthenticationTest.php`

3. **Conexión a Base de Datos:**
   - Configuración de `.env` apuntando a MySQL `auth_db` (root, sin contraseña).
   - Se añadió la columna `password` a la tabla `users` en MySQL para compatibilidad con Laravel (sincronizada con `password_hash`).
   - El modelo `User` fue adaptado con `getAuthPassword()` para soportar ambas columnas.

4. **Correcciones menores:**
   - Se habilitó la extensión `zip` de PHP en XAMPP (`php.ini`).
   - Se cambió la validación de email a `email:filter` en `RegisterRequest` para rechazar formatos como `ana@correo` (CP-03).
   - Se actualizó `ExampleTest` para esperar un redirect 302 en `/` (acorde a la ruta `Route::redirect('/', '/login')`).
   - Se configuró `phpunit.xml` con SQLite en memoria para tests.

5. **Verificación final:**
   - ✅ `php artisan route:list` — 8 rutas registradas correctamente.
   - ✅ `php artisan test` — **16/16 tests pasados** (40 assertions).
   - ✅ `php artisan serve` — Servidor funcional en `http://127.0.0.1:8000`.
   - ✅ Páginas `/login` y `/register` renderizando correctamente.

---

## 3. Próximos Pasos / Pendientes (Sprint Backlog)

> Anota aquí las tareas pendientes, decisiones por tomar o trabajo planificado para los siguientes sprints.

- [ ] **HU-03** — Recuperación de contraseña (tabla `password_resets` ya existe).
- [ ] **HU-04** — Cerrar sesión (tabla `sessions` ya existe; implementar ruta y lógica de logout).
- [ ] **HU-05** — Límite de intentos fallidos (tabla `login_attempts` ya existe; implementar middleware o lógica en `LoginController`).
- [ ] Crear migraciones Laravel formales para las tablas de `auth_db` (actualmente solo existe `DataBase.txt` con DDL puro).
- [ ] Documentar pruebas manuales con evidencia en la hoja "Ex form Testing" para el entregable.
- [ ] Revisar y completar el `README.md` con instrucciones de instalación actualizadas (ya no es necesario hacer `composer create-project`).
- [ ] Configurar `.env.example` con las variables específicas del proyecto.

---

> _Última actualización: 2026-10-25 por Samuel._
