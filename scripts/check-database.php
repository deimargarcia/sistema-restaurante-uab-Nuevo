<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Infrastructure/Database/Conexion.php';

use Restaurante\Infrastructure\Database\Conexion;

try {
    $conexion = Conexion::crearDesdeEntorno();
    $base = $conexion->query('SELECT DATABASE()')->fetchColumn();
    $tablas = $conexion->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    fwrite(STDOUT, "Conexión correcta a la base '{$base}'.\n");
    fwrite(STDOUT, 'Tablas encontradas: ' . count($tablas) . "\n");
    exit(0);
} catch (PDOException $error) {
    fwrite(STDERR, "No se pudo conectar a MySQL/MariaDB. Revisa DB_HOST, DB_PORT, DB_NAME, DB_USER y DB_PASS.\n");
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
