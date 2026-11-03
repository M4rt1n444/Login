# HU-01, HU-02 y HU-03: autenticación en Laravel

Compatible con Laravel 11 / 12 (PHP 8.2+). Sin Breeze ni Jetstream: los flujos están implementados por separado para que cada criterio de aceptación sea trazable y comprobable.

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

## Envío de correos con Gmail

El envío de recuperación requiere SMTP. La configuración de desarrollo usa Gmail
(`smtp.gmail.com`, puerto `587`, STARTTLS); el mailer `log` solo guarda el mensaje
en `storage/logs/laravel.log` y no lo entrega a una bandeja.

1. Activa la verificación en dos pasos de la cuenta Google y genera una
   [contraseña de aplicación](https://myaccount.google.com/apppasswords). No uses
   la contraseña normal de Gmail.
2. En `.env`, reemplaza `MAIL_USERNAME` por tu dirección Gmail y `MAIL_PASSWORD`
   por la contraseña de aplicación. Si dejas `MAIL_FROM_ADDRESS` vacío, la
   aplicación usará `MAIL_USERNAME` como remitente automáticamente.
3. Limpia la configuración almacenada y reinicia el servidor:

   ```bash
   php artisan config:clear
   ```

4. Solicita el enlace de recuperación. Si ya solicitaste uno, espera 60 segundos
   antes de volver a intentarlo debido al límite de reenvío. Si SMTP rechaza el
   envío, revisa el error en `storage/logs/laravel.log`.

No publiques ni compartas el archivo `.env` ni la contraseña de aplicación.

## Pruebas

Los tests automatizados usan SQLite en memoria; ejecuta:

```bash
php artisan test
```

Las pruebas de HU-03 están en `tests/Feature/PasswordResetTest.php` e incluyen respuesta genérica, envío del enlace, vencimiento, uso único del token, reglas de contraseña y cambio de credenciales. Para entregar el proyecto, documenta también las pruebas manuales con evidencia en la hoja "Ex form Testing".

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
| CA-03.1 / 03.2 | `forgot-password.blade.php` + `PasswordResetLinkController` (mensaje igual exista o no la cuenta) |
| CA-03.3 / 03.4 | Broker de Laravel, tabla `password_reset_tokens` y expiración de 30 minutos en `config/auth.php` |
| CA-03.5 | `PasswordPolicy` compartida entre `RegisterRequest` y `ResetPasswordRequest` |
| CA-03.6 / 03.7 | `NewPasswordController` (el broker consume el token y guarda la nueva contraseña) + `PasswordResetTest` |

## Listado de usuarios

Al iniciar sesión, el dashboard muestra los usuarios registrados con su ID,
nombre, correo y fecha de registro. El listado requiere autenticación, se pagina
de 10 en 10 y omite contraseñas y otros datos sensibles. Su controlador y pruebas
están en `app/Http/Controllers/UsersController.php` y `tests/Feature/UsersListTest.php`.
