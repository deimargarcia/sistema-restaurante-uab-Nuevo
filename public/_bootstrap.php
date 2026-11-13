<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Infrastructure/Autoload.php';

function rutaBasePublica(): string
{
    $ruta = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $posicionPublica = strpos($ruta, '/public/');

    return $posicionPublica === false
        ? ''
        : substr($ruta, 0, $posicionPublica) . '/public';
}

function escaparHtml(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function iniciarSesionWeb(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}
