# HU-01 (Registro) y HU-02 (Inicio de sesión) en Laravel

Compatible con Laravel 11 / 12 (PHP 8.2+). Sin Breeze ni Jetstream: todo está implementado a mano para que cada criterio de aceptación sea trazable.

## Instalación

```bash
composer create-project laravel/laravel auth-app
cd auth-app
```

1. Copia el contenido de este paquete sobre el proyecto, respetando las rutas
   (`routes/web.php` reemplaza al existente).
2. Configura la base de datos en `.env` (MySQL o PostgreSQL).
3. Ejecuta:

```bash
php artisan migrate
php artisan serve
```

4. En `bootstrap/app.php`, dentro de `->withMiddleware(...)`, agrega para que un usuario
   ya autenticado que visite /login sea enviado al área privada:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->redirectUsersTo('/dashboard');
})
```

## Pruebas

Los tests automatizados (CP-01 a CP-14) usan SQLite en memoria; en `phpunit.xml`
descomenta `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:` y corre:

```bash
php artisan test
```

(Para el entregable, igual debes documentar las pruebas manuales con evidencia en la hoja
"Ex form Testing").

## Trazabilidad

| Criterio | Dónde se cumple |
|---|---|
| CA-01.1 / 01.2 | `register.blade.php` + `RegisterRequest` (required) |
| CA-01.3 | `RegisterRequest` (email, unique, minúsculas en `prepareForValidation`) |
| CA-01.4 | `RegisterRequest` (min:8 + 3 regex) |
| CA-01.5 | `RegisterRequest` (confirmed) |
| CA-01.6 | `RegisterController@store` (flash `status`) |
| CA-01.7 | Cast `hashed` del modelo `User` (bcrypt) |
| CA-02.1 / 02.4 | `login.blade.php` + `LoginRequest` |
| CA-02.2 / 02.3 | `LoginController@store` (`Auth::attempt`, error genérico) |
| CA-02.5 | `redirect()->intended(route('dashboard'))` |
| CA-02.6 | `Route::middleware('auth')` en `routes/web.php` |
