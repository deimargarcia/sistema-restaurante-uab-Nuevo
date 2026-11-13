# Crear la base local

El esquema completo está en [`schema.sql`](schema.sql). El script crea la base
`restaurante_rumbo_al_cielo_nuevo` si no existe, la selecciona y crea sus tablas.

## Importar con phpMyAdmin

1. Inicia el servidor MySQL/MariaDB.
2. En phpMyAdmin, importa `schema.sql` desde la pestaña **Importar**. El usuario
   de la conexión necesita permiso para crear bases y tablas.
3. Configura las variables de entorno de la aplicación (`DB_HOST`, `DB_PORT`,
   `DB_NAME`, `DB_USER` y `DB_PASS`) con las credenciales locales.
4. Ejecuta `php scripts/check-database.php` desde la raíz del proyecto.

Si la base se creó con una versión anterior del esquema, aplica una sola vez la
migración `migrations/20261112_permitir_rereservar_fecha_cancelada.sql`. Esta
actualización deja que una persona vuelva a reservar una fecha después de
cancelar la reserva anterior, manteniendo la protección contra reservas activas
duplicadas. En una base nueva, `schema.sql` ya incluye ese cambio.

No se guardan contraseñas en Git. `.env.example` sirve como referencia; PHP no
carga ese archivo automáticamente, así que cada entorno debe proporcionar las
variables al proceso PHP/Apache o usar una herramienta de configuración local.

## XAMPP en Windows

Si XAMPP escucha en el puerto habitual:

```powershell
$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '3306'
$env:DB_NAME = 'restaurante_rumbo_al_cielo_nuevo'
$env:DB_USER = 'root'
$env:DB_PASS = ''
& 'C:\xampp\php\php.exe' scripts/check-database.php
```

Si MariaDB está configurado en otro puerto, cambia `DB_PORT`. Para Apache, las
variables deben estar disponibles en el proceso que ejecuta PHP, no solo en una
terminal distinta.
