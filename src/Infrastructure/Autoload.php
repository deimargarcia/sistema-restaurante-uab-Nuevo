<?php
declare(strict_types=1);

spl_autoload_register(static function (string $clase): void {
    $prefijo = 'Restaurante\\';
    if (!str_starts_with($clase, $prefijo)) {
        return;
    }

    $rutaRelativa = str_replace('\\', DIRECTORY_SEPARATOR, substr($clase, strlen($prefijo)));
    $archivo = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src'
        . DIRECTORY_SEPARATOR . $rutaRelativa . '.php';

    if (is_file($archivo)) {
        require_once $archivo;
    }
});
