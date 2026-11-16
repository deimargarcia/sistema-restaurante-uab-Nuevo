<?php
declare(strict_types=1);

use Restaurante\Adapters\Http\ControladorFamiliares;
use Restaurante\Adapters\Http\ControladorGestionFamiliares;
use Restaurante\Adapters\Repository\PdoFamiliarRepository;
use Restaurante\Application\Familiares\ActualizarFamiliar;
use Restaurante\Application\Familiares\CambiarEstadoFamiliar;
use Restaurante\Application\Familiares\ListarFamiliares;
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
    $gestion = new ControladorGestionFamiliares(
        new ListarFamiliares($repositorio),
        new ActualizarFamiliar($repositorio),
        new CambiarEstadoFamiliar($repositorio),
    );
    if ($metodo === 'GET') {
        $respuesta = $controlador->listarTitulares();
        $familiares = $gestion->listar();
        $respuesta['familiares'] = $familiares['familiares'] ?? [];
        $respuesta['exito'] = $respuesta['exito'] && $familiares['exito'];
    } else {
        $accion = (string) ($_POST['accion'] ?? 'registrar');
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $respuesta = match ($accion) {
            'registrar' => $controlador->registrar($_POST),
            'actualizar' => $gestion->actualizar($id, $_POST),
            'estado' => $gestion->cambiarEstado($id, (string) ($_POST['estado'] ?? '')),
            default => ['exito' => false, 'mensaje' => 'La acción solicitada no es válida.'],
        };
    }

    http_response_code($respuesta['exito'] ? ($metodo === 'POST' && ($_POST['accion'] ?? 'registrar') === 'registrar' ? 201 : 200) : 422);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'No se pudo completar la gestión del familiar. Intenta nuevamente.',
    ], JSON_UNESCAPED_UNICODE);
}
