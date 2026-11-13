<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

use Restaurante\Adapters\Http\ControladorPensionamientos;
use Restaurante\Adapters\Repository\PdoPensionamientoRepository;
use Restaurante\Application\Pensionamientos\AfiliarBeneficiario;
use Restaurante\Infrastructure\Database\Conexion;

iniciarSesionWeb();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['tipo_cuenta'] ?? '', ['PENSIONADO', 'NO_PENSIONADO'], true)) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'Inicia sesión como empleado para continuar.']);
    exit;
}

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) ($_POST['periodo'] ?? ''), $coincidencias)) {
    http_response_code(422);
    echo json_encode(['exito' => false, 'mensaje' => 'Selecciona un periodo válido.']);
    exit;
}

try {
    $periodo = explode('-', $coincidencias[0]);
    $controlador = new ControladorPensionamientos(
        new AfiliarBeneficiario(new PdoPensionamientoRepository(Conexion::crearDesdeEntorno()))
    );
    $resultado = $controlador->afiliarTitular((int) $_SESSION['usuario_id'], (int) $periodo[0], (int) $periodo[1]);
    http_response_code($resultado['exito'] ? 201 : 422);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'No se pudo procesar la afiliación. Inténtalo de nuevo.']);
}
