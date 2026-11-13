<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
use Restaurante\Adapters\Http\ControladorReportes;
use Restaurante\Adapters\Repository\PdoReporteRepository;
use Restaurante\Application\Reportes\ConsultarReporteMensual;
use Restaurante\Application\Reportes\GenerarReporteMensual;
use Restaurante\Infrastructure\Database\Conexion;

iniciarSesionWeb();
header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['exito'=>false,'mensaje'=>'Solo administración puede gestionar reportes.'],JSON_UNESCAPED_UNICODE);
    exit;
}
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo,['GET','POST'],true)) { http_response_code(405); echo json_encode(['exito'=>false,'mensaje'=>'Método no permitido.']); exit; }
try {
    $repo = new PdoReporteRepository(Conexion::crearDesdeEntorno());
    $controlador = new ControladorReportes(new GenerarReporteMensual($repo),new ConsultarReporteMensual($repo));
    if ($metodo === 'POST') {
        $respuesta = ($_POST['accion'] ?? '') === 'generar'
            ? $controlador->generar((int)($_POST['anio'] ?? 0),(int)($_POST['mes'] ?? 0))
            : ['exito'=>false,'mensaje'=>'Acción no válida.'];
        http_response_code($respuesta['exito'] ? 200 : 422);
    } else {
        $periodo = (string)($_GET['periodo'] ?? '');
        if (!preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/',$periodo,$coincidencias)) {
            $respuesta=['exito'=>false,'mensaje'=>'Selecciona un mes válido.']; http_response_code(422);
        } else {
            $respuesta=$controlador->consultar((int)$coincidencias[1],(int)$coincidencias[2]); http_response_code(200);
        }
    }
    echo json_encode($respuesta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log($error->__toString()); http_response_code(500);
    echo json_encode(['exito'=>false,'mensaje'=>'No se pudo procesar el reporte. Inténtalo de nuevo.'],JSON_UNESCAPED_UNICODE);
}
