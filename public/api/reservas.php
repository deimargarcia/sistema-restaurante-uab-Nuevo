<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

use Restaurante\Adapters\Http\ControladorReservas;
use Restaurante\Adapters\Repository\PdoReservaRepository;
use Restaurante\Application\Reservas\CancelarReserva;
use Restaurante\Application\Reservas\CrearReserva;
use Restaurante\Application\Reservas\ListarReservasTitular;
use Restaurante\Application\Reservas\ListarReservasDia;
use Restaurante\Application\Reservas\RegistrarEntrega;
use Restaurante\Infrastructure\Database\Conexion;

iniciarSesionWeb();
header('Content-Type: application/json; charset=utf-8');
$titularId = (int) ($_SESSION['usuario_id'] ?? 0);
$tipoCuenta = $_SESSION['tipo_cuenta'] ?? '';
if ($titularId < 1 || !in_array($tipoCuenta, ['ADMIN', 'PENSIONADO', 'NO_PENSIONADO'], true)) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'Inicia sesión con una cuenta de empleado para continuar.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $repositorio = new PdoReservaRepository(Conexion::crearDesdeEntorno());
    $controlador = new ControladorReservas(
        new CrearReserva($repositorio),
        new CancelarReserva($repositorio),
        new ListarReservasTitular($repositorio),
        new ListarReservasDia($repositorio),
        new RegistrarEntrega($repositorio),
    );
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($tipoCuenta === 'ADMIN') {
            $fecha = (string) ($_GET['fecha'] ?? '');
            $fechaValida = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
            $respuesta = $fechaValida && $fechaValida->format('Y-m-d') === $fecha
                ? $controlador->listarDia($fecha)
                : ['exito' => false, 'mensaje' => 'Selecciona una fecha válida para el consolidado.'];
        } else {
            $respuesta = $controlador->listar($titularId);
        }
        http_response_code($respuesta['exito'] ? 200 : 403);
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($tipoCuenta === 'ADMIN' && ($_POST['accion'] ?? '') === 'registrar_entrega') {
        $reservaId = filter_var($_POST['reserva_id'] ?? null, FILTER_VALIDATE_INT);
        $respuesta = $reservaId
            ? $controlador->registrarEntrega($reservaId)
            : ['exito' => false, 'mensaje' => 'La reserva seleccionada no es válida.'];
        http_response_code($respuesta['exito'] ? 200 : 422);
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($tipoCuenta === 'ADMIN') {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'Administración consulta reservas; no reserva almuerzos para sí misma.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (($_POST['accion'] ?? '') === 'cancelar') {
        $reservaId = filter_var($_POST['reserva_id'] ?? null, FILTER_VALIDATE_INT);
        $respuesta = $reservaId
            ? $controlador->cancelar($titularId, $reservaId)
            : ['exito' => false, 'mensaje' => 'La reserva seleccionada no es válida.'];
        http_response_code($respuesta['exito'] ? 200 : 422);
    } else {
        $familiarTexto = trim((string) ($_POST['familiar_id'] ?? ''));
        $familiarId = $familiarTexto === '' ? null : filter_var($familiarTexto, FILTER_VALIDATE_INT);
        if ($familiarTexto !== '' && (!$familiarId || $familiarId < 1)) {
            $respuesta = ['exito' => false, 'mensaje' => 'El familiar seleccionado no es válido.'];
        } else {
            $respuesta = $controlador->crear($titularId, $familiarId ?: null, (string) ($_POST['fecha'] ?? ''));
        }
        http_response_code($respuesta['exito'] ? 201 : 422);
    }
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'No se pudo procesar la reserva. Inténtalo de nuevo.'], JSON_UNESCAPED_UNICODE);
}
