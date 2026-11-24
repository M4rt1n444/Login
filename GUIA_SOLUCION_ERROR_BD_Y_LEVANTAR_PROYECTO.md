# 🛠️ Guía Rápida para el Compañero: Solución al Error de Conexión a Base de Datos y Puesta en Marcha

> **Para el compañero (o si estás usando un asistente IA como Antigravity / Cursor / Copilot):**  
> Si estás viendo el error:  
> `SQLSTATE[HY000] [2002] No se puede establecer una conexión ya que el equipo de destino denegó expresamente dicha conexión (Connection: mysql...)`  
> **El problema es 100% local en tu máquina:** el servicio de **MySQL está apagado en tu XAMPP** o aún no has creado la base de datos ni ejecutado las migraciones. El código del repositorio funciona perfectamente.

---

## 🤖 Si estás usando Antigravity o un Asistente IA
Solo cópiale y pégale este mensaje a tu Antigravity en el chat:
> *"Hola Antigravity, por favor lee el archivo `GUIA_SOLUCION_ERROR_BD_Y_LEVANTAR_PROYECTO.md` y realiza todos los pasos necesarios para iniciar MySQL en mi equipo, asegurar que exista la base de datos `auth_db`, configurar mi `.env` y ejecutar `php artisan migrate` para que el proyecto quede funcionando."*

---

## 👨‍💻 Si lo vas a hacer tú mismo paso a paso (Tarda 2 minutos):

### Paso 1: Iniciar MySQL en XAMPP (Causa principal del error)
El error `denegó expresamente dicha conexión` ocurre porque MySQL no está corriendo en el puerto 3306.
1. Abre tu **XAMPP Control Panel**.
2. Al lado del módulo **MySQL**, haz clic en el botón **Start**.
   *(Debe ponerse en color verde y mostrar el puerto 3306)*.
   > **Tip si no abre el panel:** También puedes iniciarlo desde tu terminal ejecutando:  
   > `C:\xampp\mysql_start.bat`

---

### Paso 2: Asegurar el archivo `.env`
Recuerda que por seguridad el archivo `.env` nunca se sube a GitHub, por lo que cada uno tiene el suyo en su computadora.
Verifica que en tu carpeta raíz tengas un archivo llamado `.env` con esta configuración de base de datos:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auth_db
DB_USERNAME=root
DB_PASSWORD=
```
*(Si no tienes el archivo `.env`, copia el archivo `.env.example`, cámbiale el nombre a `.env` y ejecuta `php artisan key:generate`)*.

---

### Paso 3: Crear la Base de Datos y Correr las Migraciones
Abre una terminal (PowerShell o CMD) en la carpeta de tu proyecto y ejecuta:

```powershell
# 1. Ejecutar las migraciones (si te pregunta si deseas crear la BD 'auth_db', escribe 'yes'):
php artisan migrate

# 2. Opcional: si la BD no existe y quieres asegurarte de crearla por comando MySQL:
# C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS auth_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

### Paso 4: Comprobar que todo funcione

1. **Verificar las pruebas automatizadas (Sprint 3):**
   ```powershell
   php artisan test
   ```
   *(Deben pasar las **60 pruebas** en verde al 100%, 0 errores).*

2. **Levantar el servidor web:**
   ```powershell
   php artisan serve
   ```
3. **Probar en tu navegador:**
   - Abre: `http://127.0.0.1:8000/register`
   - Regístrate con tu correo (por ejemplo `mabonilla@uniempresarial.edu.co`).
   - Verás que ya no saldrá el error de conexión y podrás ingresar al Dashboard y al módulo **Mis Tareas y Calendario**.

---

### 📋 Para las Pruebas y el Excel (Sprint 3):
En este mismo proyecto tienes el archivo:  
📄 **`INSTRUCCIONES_PRUEBAS_CAJA_BLANCA.md`**  
Allí tienes:
- La tabla de las **pruebas unitarias lista con el patrón AAA (Arrange - Act - Assert) para copiar y pegar directamente en el Excel**.
- La guía para activar Xdebug y generar el reporte HTML de cobertura con:
  ```powershell
  php artisan test --coverage-html coverage
  ```
