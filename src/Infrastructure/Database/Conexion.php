<?php
declare(strict_types=1);

namespace Restaurante\Infrastructure\Database;

use PDO;

final class Conexion
{
    public static function crearDesdeEntorno(): PDO
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $puerto = getenv('DB_PORT') ?: '3306';
        $base = getenv('DB_NAME') ?: 'restaurante_rumbo_al_cielo_nuevo';
        $usuario = getenv('DB_USER') ?: 'root';
        $clave = getenv('DB_PASS') ?: '';
        $dsn = "mysql:host={$host};port={$puerto};dbname={$base};charset=utf8mb4";

        return new PDO($dsn, $usuario, $clave, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
