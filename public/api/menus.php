<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

use Restaurante\Adapters\Http\ControladorMenus;
use Restaurante\Adapters\Repository\PdoMenuRepository;
use Restaurante\Application\Menus\ConsultarMenuSemana;
use Restaurante\Application\Menus\PublicarMenu;
use Restaurante\Infrastructure\Database\Conexion;

iniciarSesionWeb();
header('Content-Type: application/json; charset=utf-8');
$rol = $_SESSION['tipo_cuenta'] ?? '';
if (empty($_SESSION['usuario_id']) || !in_array($rol, ['ADMIN', 'PENSIONADO', 'NO_PENSIONADO'], true)) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'Inicia sesión para continuar.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo, ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $repo = new PdoMenuRepository(Conexion::crearDesdeEntorno());
    $controlador = new ControladorMenus(new PublicarMenu($repo), new ConsultarMenuSemana($repo));
    if ($metodo === 'GET') {
        $respuesta = $controlador->consultarSemana(isset($_GET['fecha']) ? (string) $_GET['fecha'] : null);
        http_response_code($respuesta['exito'] ? 200 : 422);
    } elseif ($rol !== 'ADMIN') {
        $respuesta = ['exito' => false, 'mensaje' => 'Solo administración puede publicar menús.'];
        http_response_code(403);
    } else {
        $respuesta = $controlador->publicar($_POST, (int) $_SESSION['usuario_id']);
        http_response_code($respuesta['exito'] ? 201 : 422);
    }
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'No se pudo procesar el menú. Inténtalo de nuevo.'], JSON_UNESCAPED_UNICODE);
}
