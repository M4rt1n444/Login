# 🚀 Guía de Instalación y Puesta en Marcha Local (Sin Errores 400 / 500)

Esta guía contiene el paso a paso exacto que debe seguir cualquier integrante del equipo al clonar o actualizar el repositorio en su computadora para que el proyecto corra al 100% sin errores de servidor (500), claves faltantes, errores de base de datos o fallos de sesión.

---

## 📌 Requisitos Previos en la Máquina
1. **XAMPP** instalado (con **Apache** y **MySQL** iniciados).
2. **PHP >= 8.2** (incluido en XAMPP).
3. **Composer** instalado globalmente ([getcomposer.org](https://getcomposer.org/)).
4. **Git** instalado.

---

## 🛠️ ¿Por qué ocurrían los Errores 500 y 400? (Diagnóstico)

1. **Error 500 ("No application encryption key has been specified"):**
   * *Causa:* El archivo `.env` no viene en Git (por seguridad). Si no se crea y no se ejecuta `php artisan key:generate`, Laravel no puede cifrar cookies ni sesiones.
2. **Error 500 ("Table 'auth_db.sessions' doesn't exist" o "Unknown database 'auth_db'"):**
   * *Causa:* MySQL no está encendido en XAMPP, la base de datos `auth_db` no fue creada en phpMyAdmin, o las sesiones estaban configuradas para guardarse en la BD antes de crear las tablas.
3. **Error 500 ("Connection to smtp.gmail.com could not be established"):**
   * *Causa:* Al probar "Recuperar contraseña", Laravel intentaba conectarse a un servidor de Gmail falso. Ahora está configurado con `MAIL_MAILER=log`, por lo que escribe el enlace directamente en `storage/logs/laravel.log` de forma segura.
4. **Error 400 / 419 ("Page Expired" o CSRF mismatch):**
   * *Causa:* Problemas con el almacenamiento de sesiones o falta de permisos en las carpetas `storage/`.

---

## 📋 Paso a Paso para un Nuevo Integrante (Clonación Inicial)

### Paso 1: Clonar el Repositorio
Abre tu consola (Git Bash o PowerShell) en la carpeta `C:\xampp\htdocs\` y clona:
```bash
cd C:\xampp\htdocs
git clone https://github.com/M4rt1n444/Login.git
cd Login
```

---

### Paso 2: Crear el archivo de entorno `.env`
Copia el archivo `.env.example` pre-configurado para generar tu `.env` local:

* En **Windows PowerShell**:
  ```powershell
  copy .env.example .env
  ```
* En **Git Bash** o terminal estándar:
  ```bash
  cp .env.example .env
  ```

*(Nota: `.env.example` ya viene listo con la conexión a MySQL `auth_db`, usuario `root`, sesión en archivo y correo en log).*

---

### Paso 3: Instalar dependencias de Composer
Descarga todas las librerías necesarias del framework:
```bash
composer install
```

---

### Paso 4: Generar la Clave Secreta de Laravel (`APP_KEY`)
Genera la clave única de encriptación en tu `.env` (esto soluciona el error 500 de clave faltante):
```bash
php artisan key:generate
```

---

### Paso 5: Preparar la Base de Datos en XAMPP

1. Abre el panel de control de **XAMPP** y dale **Start** a **Apache** y a **MySQL**.
2. Entra a tu navegador a: [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Haz clic en **Nueva** base de datos en el menú izquierdo.
4. Escribe de nombre exacto: `auth_db` con cotejamiento `utf8mb4_unicode_ci` y presiona **Crear**.
5. Ahora en la consola del proyecto, corre las migraciones automáticas de Laravel:
   ```bash
   php artisan migrate
   ```
   *(Esto creará automáticamente las tablas `users`, `password_reset_tokens`, `sessions`, `login_attempts`, `cache` y `jobs`).*

---

### Paso 6: Levantar el Servidor Local
Ejecuta el servidor de desarrollo de Laravel:
```bash
php artisan serve
```

Abre tu navegador web en:
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔄 ¿Qué hacer si ya tenías el proyecto y solo hiciste `git pull`?

Si un compañero ya tenía el repositorio clonado y baja los nuevos cambios que subiste:
```bash
git pull origin main
composer install
php artisan migrate
```
*(Y verificar que en su archivo `.env` local tenga `MAIL_MAILER=log` y `SESSION_DRIVER=file`).*

---

## 🧪 Información para Pruebas (Instrucciones del Docente)

Para el compañero encargado de pruebas y control de calidad:

### 1. Ejecutar Todas las Pruebas en Consola
El comando oficial para correr la suite de pruebas es:
```bash
php artisan test
```

### 2. Pruebas Funcionales (Sprint 1 y 2 - Feature Tests)
Se encuentran en `tests/Feature/`:
* `tests/Feature/AuthenticationTest.php`:
  * **HU-01:** CP-01 al CP-08 (Registro, validaciones de campos, contraseñas seguras, unicidad en minúsculas).
  * **HU-02:** CP-09 al CP-14 (Login válido, credenciales erróneas genéricas, protección de ruta privada).
  * **HU-04:** CP-15 al CP-17 (Cierre de sesión, invalidación de sesión y bloqueo para visitantes).
  * **HU-05:** CP-18 y CP-19 (Bloqueo tras 5 intentos fallidos en 15 min y reinicio del contador al autenticar).
* `tests/Feature/PasswordResetTest.php`:
  * **HU-03:** Pruebas completas del flujo de recuperación de contraseña, token de un solo uso y expiración a 30 minutos.
* `tests/Feature/UsersListTest.php`:
  * Vista de usuarios en área privada sin campos sensibles y paginación.

### 3. Pruebas Unitarias (Unit Tests - Patrón Arrange / Act / Assert)
Se encuentran en `tests/Unit/PasswordPolicyTest.php`:
Para ejecutarlas únicamente a ellas:
```bash
php artisan test --testsuite=Unit
```
* Siguen estrictamente el patrón **AAA (Arrange - Act - Assert)** que solicitó el profesor:
  1. **Arrange (Preparar):** Se definen los datos de prueba (ej. contraseña a evaluar).
  2. **Act (Ejecutar):** Se ejecuta la regla o validador `PasswordPolicy::validationRules()`.
  3. **Assert (Afirmar/Validar):** Se valida si la condición es verdadera o falsa (`assertTrue`, `assertFalse`).

