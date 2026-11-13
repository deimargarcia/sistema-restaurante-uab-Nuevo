<?php
declare(strict_types=1);

use Restaurante\Adapters\Http\ControladorEmpleados;
use Restaurante\Adapters\Repository\PdoEmpleadoRepository;
use Restaurante\Application\Empleados\RegistrarEmpleado;
use Restaurante\Infrastructure\Database\Conexion;

require_once __DIR__ . '/../_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Usa el formulario de administración.']);
    exit;
}

iniciarSesionWeb();
if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'Solo administración puede registrar empleados.']);
    exit;
}

try {
    $repositorio = new PdoEmpleadoRepository(Conexion::crearDesdeEntorno());
    $controlador = new ControladorEmpleados(new RegistrarEmpleado($repositorio));
    $respuesta = $controlador->registrar($_POST);

    http_response_code($respuesta['exito'] ? 201 : 422);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'No se pudo guardar el empleado. Intenta nuevamente.',
    ], JSON_UNESCAPED_UNICODE);
}
