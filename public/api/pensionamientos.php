<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

use Restaurante\Adapters\Http\ControladorPensionamientos;
use Restaurante\Adapters\Repository\PdoPensionamientoRepository;
use Restaurante\Application\Pensionamientos\AfiliarBeneficiario;
use Restaurante\Infrastructure\Database\Conexion;

iniciarSesionWeb();
header('Content-Type: application/json; charset=utf-8');

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['tipo_cuenta'] ?? '', ['ADMIN', 'PENSIONADO', 'NO_PENSIONADO'], true)) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'Inicia sesión con una cuenta habilitada para continuar.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $repositorio = new PdoPensionamientoRepository(Conexion::crearDesdeEntorno());
    $controlador = new ControladorPensionamientos(
        new AfiliarBeneficiario($repositorio)
    );

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (($_SESSION['tipo_cuenta'] ?? '') === 'ADMIN' && empty($_GET['titular_id'])) {
            $resultado = $controlador->listarTitularesPensionados();
        } else {
            $titularId = ($_SESSION['tipo_cuenta'] ?? '') === 'ADMIN'
                ? filter_var($_GET['titular_id'] ?? null, FILTER_VALIDATE_INT)
                : (int) $_SESSION['usuario_id'];
            $resultado = $titularId
                ? $controlador->listarFamiliares($titularId)
                : ['exito' => false, 'mensaje' => 'El titular seleccionado no es válido.'];
        }
        http_response_code($resultado['exito'] ? 200 : 403);
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) ($_POST['periodo'] ?? ''), $coincidencias)) {
        http_response_code(422);
        echo json_encode(['exito' => false, 'mensaje' => 'Selecciona un periodo válido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    [$anio, $mes] = array_map('intval', explode('-', $coincidencias[0]));
    $tipoSesion = $_SESSION['tipo_cuenta'] ?? '';
    $titularId = $tipoSesion === 'ADMIN'
        ? filter_var($_POST['titular_id'] ?? null, FILTER_VALIDATE_INT)
        : (int) $_SESSION['usuario_id'];
    if (!$titularId || $titularId < 1) {
        http_response_code(422);
        echo json_encode(['exito' => false, 'mensaje' => 'Selecciona un titular válido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $familiarEnviado = trim((string) ($_POST['familiar_id'] ?? ''));
    $familiarId = $familiarEnviado === '' ? null : filter_var($familiarEnviado, FILTER_VALIDATE_INT);
    if ($familiarEnviado !== '' && (!$familiarId || $familiarId < 1)) {
        http_response_code(422);
        echo json_encode(['exito' => false, 'mensaje' => 'El familiar seleccionado no es válido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $resultado = $familiarId !== null
        ? $controlador->afiliarFamiliar($titularId, $familiarId, $anio, $mes, (int) $_SESSION['usuario_id'])
        : $controlador->afiliarTitular($titularId, $anio, $mes, (int) $_SESSION['usuario_id']);
    http_response_code($resultado['exito'] ? 201 : 422);
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'No se pudo procesar la afiliación. Inténtalo de nuevo.']);
}
