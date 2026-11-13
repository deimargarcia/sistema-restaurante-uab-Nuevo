# Sistema de reservas del restaurante Rumbo al Cielo

Este repositorio contiene la nueva versión del sistema de almuerzos de la Universidad Adventista de Bolivia. Vamos trasladando las funciones desde el proyecto anterior por partes, para poder revisar y probar cada regla antes de seguir con la siguiente.

## Cómo está organizado

El sistema separa las reglas del restaurante de la web y de MySQL. Así, los casos de uso se pueden probar sin levantar el servidor ni conectarse a la base.

```mermaid
flowchart TD
    Web[Controladores y páginas] --> Caso[Caso de uso]
    Caso --> Regla[Dominio: entidades y reglas]
    Caso --> Puerto[Contrato de repositorio]
    Repositorio[Repositorio PDO] -. cumple .-> Puerto
    Repositorio --> Conexion[Conexión PDO]
    Conexion --> Base[(MySQL o MariaDB)]
    Pruebas[Pruebas unitarias] --> Caso
```

| Carpeta | Qué contiene |
|---|---|
| `src/Domain` | Entidades y reglas propias del restaurante. |
| `src/Application` | Casos de uso y contratos que estos necesitan. |
| `src/Adapters/Http` | Controladores que reciben solicitudes de la web. |
| `src/Adapters/Repository` | Consultas y escrituras que adaptan los casos de uso a la base. |
| `src/Infrastructure` | Conexión PDO y configuración del entorno. |
| `database` | Esquema SQL para crear las tablas. |
| `tests/Unit` | Pruebas que ejecutan los casos de uso con datos en memoria. |
| `reports` | Reporte PHPUnit generado al correr las pruebas. |

## Lo que ya está trasladado

El acceso acepta correo o CI, valida la contraseña guardada como hash y rechaza cuentas inactivas. Desde su panel, administración puede dar de alta empleados, vincular familiares y asistir con las afiliaciones mensuales. Los empleados pueden solicitar su afiliación para el siguiente periodo; se programan almuerzos de lunes a jueves y se omiten feriados registrados. El titular pensionado también puede afiliar a sus familiares activos. Se validan los datos obligatorios, la unicidad de correo, CI y código, y los parentescos permitidos. Las contraseñas se guardan como hash.

La relación entre las historias, sus criterios y las pruebas que ya existen está en [`docs/trazabilidad-historias.md`](docs/trazabilidad-historias.md).

## Base de datos local

`database/schema.sql` crea la base `restaurante_rumbo_al_cielo_nuevo` y las tablas que necesita el sistema. La base local ya creada contiene la estructura inicial, sin copiar los registros personales del proyecto anterior.

Para configurar otra instalación, sigue los pasos de [`database/README.md`](database/README.md). El comando `php scripts/check-database.php` confirma la conexión y muestra cuántas tablas encuentra.

Para abrir la pantalla durante el desarrollo, configura las variables de base de datos como se explica en `database/README.md` y ejecuta desde la carpeta del proyecto:

```powershell
& 'C:\xampp\php\php.exe' -S 127.0.0.1:4173 -t public
```

Luego visita `http://127.0.0.1:4173`. En XAMPP también puedes apuntar Apache a la carpeta `public`.

## Pruebas

Se necesita PHP 8.2 y Composer. Desde la carpeta del proyecto, ejecuta:

```bash
composer install
composer test
composer test:report
```

Las pruebas del acceso cubren correo, CI, contraseña incorrecta, cuenta inactiva, campos vacíos y el panel asignado a cada rol. El reporte se guarda en `reports/phpunit.xml`.
