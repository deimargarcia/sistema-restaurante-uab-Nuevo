<?php
declare(strict_types=1);

use Restaurante\Adapters\Http\ControladorFamiliares;
use Restaurante\Adapters\Repository\PdoFamiliarRepository;
use Restaurante\Application\Familiares\RegistrarFamiliar;
use Restaurante\Infrastructure\Database\Conexion;

require_once __DIR__ . '/../_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
iniciarSesionWeb();

if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'Solo administración puede gestionar familiares.']);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo, ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

try {
    $repositorio = new PdoFamiliarRepository(Conexion::crearDesdeEntorno());
    $controlador = new ControladorFamiliares(new RegistrarFamiliar($repositorio), $repositorio);
    $respuesta = $metodo === 'GET'
        ? $controlador->listarTitulares()
        : $controlador->registrar($_POST);

    http_response_code($respuesta['exito'] ? ($metodo === 'POST' ? 201 : 200) : 422);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'No se pudo completar la gestión del familiar. Intenta nuevamente.',
    ], JSON_UNESCAPED_UNICODE);
}
