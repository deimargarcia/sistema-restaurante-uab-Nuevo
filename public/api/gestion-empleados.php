<?php
declare(strict_types=1);

use Restaurante\Adapters\Http\ControladorGestionEmpleados;
use Restaurante\Adapters\Repository\PdoEmpleadoRepository;
use Restaurante\Application\Empleados\ActualizarEmpleado;
use Restaurante\Application\Empleados\CambiarEstadoEmpleado;
use Restaurante\Application\Empleados\ListarEmpleados;
use Restaurante\Infrastructure\Database\Conexion;

require_once __DIR__ . '/../_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
iniciarSesionWeb();

if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'Solo administración puede gestionar empleados.']);
    exit;
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo, ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

try {
    $repositorio = new PdoEmpleadoRepository(Conexion::crearDesdeEntorno());
    $controlador = new ControladorGestionEmpleados(
        new ListarEmpleados($repositorio),
        new ActualizarEmpleado($repositorio),
        new CambiarEstadoEmpleado($repositorio),
    );

    if ($metodo === 'GET') {
        $respuesta = $controlador->listar();
    } else {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $accion = (string) ($_POST['accion'] ?? '');
        $respuesta = match ($accion) {
            'actualizar' => $controlador->actualizar($id, $_POST),
            'estado' => $controlador->cambiarEstado($id, ($_POST['activo'] ?? '') === '1'),
            default => ['exito' => false, 'mensaje' => 'La acción solicitada no es válida.'],
        };
    }

    http_response_code($respuesta['exito'] ? 200 : 422);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'No se pudo completar la gestión de usuarios.']);
}
