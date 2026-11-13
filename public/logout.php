<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    exit('Usa el formulario para cerrar sesión.');
}

iniciarSesionWeb();
$_SESSION = [];
session_destroy();
header('Location: ' . rutaBasePublica() . '/index.php');
exit;
