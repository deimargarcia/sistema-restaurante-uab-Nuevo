<?php
declare(strict_types=1);

use Restaurante\Adapters\Http\ControladorAcceso;
use Restaurante\Adapters\Repository\PdoUsuarioRepository;
use Restaurante\Application\Auth\IniciarSesion;
use Restaurante\Infrastructure\Database\Conexion;

require_once __DIR__ . '/../_bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Usa el formulario de inicio de sesión.']);
    exit;
}

try {
    $usuarios = new PdoUsuarioRepository(Conexion::crearDesdeEntorno());
    $casoDeUso = new IniciarSesion($usuarios);
    $controlador = new ControladorAcceso($casoDeUso, rutaBasePublica());

    $respuesta = $controlador->iniciarSesion(
        (string) ($_POST['correo_o_ci'] ?? ''),
        (string) ($_POST['contrasena'] ?? ''),
    );

    if (!$respuesta['exito']) {
        http_response_code(401);
    }

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'No se pudo completar el inicio de sesión. Intenta nuevamente.',
    ], JSON_UNESCAPED_UNICODE);
}
