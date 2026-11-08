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
| HU-03 | Recuperar contraseña | ✅ Completada | Controladores (`PasswordResetLinkController`, `NewPasswordController`), vistas, política de seguridad compartida, expiración a 30 min y tests. Configurado a driver `log` para pruebas locales seguras. |
| HU-04 | Cerrar sesión        | ✅ Completada | Método `destroy()` en `LoginController`, ruta protegida `POST /logout`, invalidación de sesión + regeneración de token CSRF y botón en dashboard. |
| HU-05 | Límite de intentos   | ✅ Completada | `RateLimiter` (máx 5 intentos en ventana de 15 min por email e IP), registro en `login_attempts`, mensaje comprensible y reinicio al autenticar. |

### Pruebas Automatizadas

- **Pruebas Funcionales (Feature):**
  - `AuthenticationTest.php`: Cubre HU-01 (CP-01 a CP-08), HU-02 (CP-09 a CP-14), HU-04 (CP-15 a CP-17) y HU-05 (CP-18 a CP-19).
  - `PasswordResetTest.php`: Cubre flujo completo de HU-03 (enlace, reseteo, token único, expiración de 30 min, validación de contraseña).
  - `UsersListTest.php`: Verifica listado paginado en área privada sin exponer campos sensibles.
- **Pruebas Unitarias (Unit):**
  - `PasswordPolicyTest.php`: 4 pruebas unitarias que validan la regla de negocio de contraseñas bajo el patrón **Arrange - Act - Assert (AAA)**.

### Archivos Clave y Rutas

| Categoría       | Ruta                                                      | Archivos                                             |
| --------------- | --------------------------------------------------------- | ---------------------------------------------------- |
| Controladores   | `app/Http/Controllers/`                                   | `LoginController.php`, `RegisterController.php`, `UsersController.php` |
| Controladores Auth | `app/Http/Controllers/Auth/`                           | `PasswordResetLinkController.php`, `NewPasswordController.php` |
| Requests         | `app/Http/Requests/`                                      | `LoginRequest.php`, `RegisterRequest.php`, `SendPasswordResetLinkRequest.php`, `ResetPasswordRequest.php` |
| Modelo           | `app/Models/`                                             | `User.php`                                           |
| Vistas           | `resources/views/`                                        | `login.blade.php`, `register.blade.php`, `dashboard.blade.php` |
| Vistas Auth      | `resources/views/auth/`                                   | `login.blade.php`, `register.blade.php`, `forgot-password.blade.php`, `reset-password.blade.php` |
| Layout           | `resources/views/layouts/`                                | `app.blade.php`                                      |
| Rutas            | `routes/`                                                 | `web.php`                                            |
| Tests Feature    | `tests/Feature/`                                          | `AuthenticationTest.php`, `PasswordResetTest.php`, `UsersListTest.php` |
| Tests Unit       | `tests/Unit/`                                             | `PasswordPolicyTest.php`                             |
| Migraciones      | `database/migrations/`                                    | `...create_users_table.php`, `...create_cache_table.php`, `...create_jobs_table.php`, `...create_login_attempts_table.php` |
| Script BD        | raíz                                                      | `DataBase.txt`                                       |
| Config local     | raíz                                                      | `.env`, `.env.example`                               |
| Bootstrap        | `bootstrap/`                                              | `app.php`                                            |

### Trazabilidad de Criterios de Aceptación

| Criterio            | Dónde se cumple                                                                |
| ------------------- | ------------------------------------------------------------------------------ |
| CA-01.1 / CA-01.2   | `register.blade.php` + `RegisterRequest` (campos required)                     |
| CA-01.3             | `RegisterRequest` → `prepareForValidation` (email a minúsculas) + `unique`     |
| CA-01.4             | `RegisterRequest` / `PasswordPolicy` (min:8, regex mayúscula, minúscula, número) |
| CA-01.5             | `RegisterRequest` (confirmed)                                                  |
| CA-01.6             | `RegisterController@store` (flash `status`)                                    |
| CA-01.7             | Cast `hashed` en modelo `User` (bcrypt automático)                             |
| CA-02.1 / CA-02.4   | `login.blade.php` + `LoginRequest`                                             |
| CA-02.2 / CA-02.3   | `LoginController@store` (`Auth::attempt`, error genérico)                      |
| CA-02.5             | `redirect()->intended(route('dashboard'))`                                     |
| CA-02.6             | `Route::middleware('auth')` en `routes/web.php`                                |
| CA-03.1 / CA-03.2   | `forgot-password.blade.php` + `PasswordResetLinkController@store` (mensaje uniforme) |
| CA-03.3 / CA-03.6   | Broker nativo de Laravel (`Password::reset`), token seguro y de un solo uso   |
| CA-03.4             | `config/auth.php` (`expire => 30` minutos)                                     |
| CA-03.5             | `ResetPasswordRequest` reutiliza `PasswordPolicy::validationRules()`           |
| CA-03.7             | Invalida credenciales previas al resetear                                      |
| CA-04.1             | Botón "Cerrar sesión" en `dashboard.blade.php`                                 |
| CA-04.2             | `LoginController@destroy` (`Auth::logout()`, `$request->session()->invalidate()`) |
| CA-04.3             | `LoginController@destroy` redirige a `route('login')`                          |
| CA-04.4             | `Route::middleware('auth')` bloquea navegación posterior a `/dashboard`       |
| CA-05.1             | `LoginController`: `RateLimiter::tooManyAttempts($throttleKey, 5)` (email + IP) |
| CA-05.2             | `RateLimiter::hit($throttleKey, 900)` (bloqueo por 15 minutos / 900 segundos)  |
| CA-05.3             | Mensaje de error genérico informando el tiempo restante en minutos             |
| CA-05.4             | Desbloqueo automático al expirar la ventana de 15 minutos                      |
| CA-05.5             | `RateLimiter::clear($throttleKey)` al autenticarse con éxito                   |

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

### 2026-11-08 — Finalización de Desarrollo (HU-03, HU-04 y HU-05), Pruebas Unitarias AAA y Guía de Despliegue

**Responsable:** Alejandro Molina (alejandromolinajuan-maker)

**Cambios realizados:**

1. **HU-03 — Recuperación de Contraseña:**
   - Enlace "¿Olvidaste tu contraseña?" añadido en la vista de login.
   - Vistas Blade y controladores de recuperación vinculados.
   - Configuración de correo ajustada a `MAIL_MAILER=log` para evitar fallos 500 en pruebas locales.
   - Expiración de enlaces configurada a 30 minutos según CA-03.4.

2. **HU-04 — Cerrar Sesión:**
   - Ruta protegida `POST /logout` agregada en `routes/web.php`.
   - Método `destroy()` implementado en `LoginController` con invalidación de sesión en servidor y regeneración de token CSRF.
   - Botón visible de "Cerrar sesión" añadido en `dashboard.blade.php`.
   - Navegación protegida: no se permite volver a ingresar a `/dashboard` sin autenticarse.

3. **HU-05 — Límite de Intentos Fallidos:**
   - Control de tasa implementado con `RateLimiter` (máximo 5 intentos fallidos consecutivos en 15 minutos por cuenta e IP).
   - Bloqueo temporal con mensaje amigable sin revelar información confidencial.
   - Reinicio del contador de intentos al autenticarse exitosamente.
   - Registro de intentos en la tabla `login_attempts`.
   - Creación de migración formal `2026_11_08_000003_create_login_attempts_table.php`.

4. **Pruebas Automatizadas Unitarias y Funcionales:**
   - Creado `tests/Unit/PasswordPolicyTest.php` con 4 pruebas unitarias documentadas bajo el patrón **Arrange - Act - Assert (AAA)**.
   - Ampliado `tests/Feature/AuthenticationTest.php` con casos CP-15 a CP-17 (Logout) y CP-18 a CP-19 (Límite de intentos).

5. **Entorno y Soporte al Equipo:**
   - Configurado `.env.example` pre-adaptado para XAMPP y MySQL `auth_db`.
   - Redactado el documento `GUIA_INSTALACION_EQUIPO.md` con la solución a errores 400/500 y el paso a paso para el equipo.

---

## 3. Estado de Tareas del Sprint Backlog

- [x] **HU-01** — Registro de usuario (completo y probado).
- [x] **HU-02** — Inicio de sesión (completo y probado).
- [x] **HU-03** — Recuperación de contraseña (completo con token de 30 min y mailer log).
- [x] **HU-04** — Cerrar sesión (completo con invalidación de sesión y botón en interfaz).
- [x] **HU-05** — Límite de intentos fallidos (completo con bloqueo en 15 min y registro de intentos).
- [x] Crear migraciones Laravel formales para `login_attempts` y tablas del sistema.
- [x] Diseñar e implementar pruebas unitarias bajo patrón AAA (`tests/Unit/PasswordPolicyTest.php`).
- [x] Configurar `.env.example` optimizado para evitar errores 500 en compañeros de equipo.
- [x] Crear guía de instalación y despliegue local (`GUIA_INSTALACION_EQUIPO.md`).
- [ ] Documentar pruebas manuales con evidencia en la hoja Excel del proyecto (a cargo del equipo de testing).

---

> _Última actualización: 2026-11-08 por Alejandro Molina._
