<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}

$nombreDashboard = trim((string) ($_SESSION['nombre'] ?? 'Administración'));
$tipoDashboard = 'ADMIN';
$rolDashboard = 'ADMINISTRACIÓN';
$subtituloDashboard = 'Cuenta administradora';
$tituloDashboard = 'Panel de administración';
$gruposNavegacion = [
    ['titulo' => 'PRINCIPAL', 'enlaces' => [
        ['texto' => 'Inicio', 'ruta' => '/admin/dashboard.php', 'icono' => 'inicio', 'activo' => true],
    ]],
    ['titulo' => 'PERSONAS', 'enlaces' => [
        ['texto' => 'Empleados', 'ruta' => '/admin/empleados.php', 'icono' => 'personas'],
        ['texto' => 'Familiares', 'ruta' => '/admin/familiares.php', 'icono' => 'familia'],
        ['texto' => 'Afiliaciones', 'ruta' => '/admin/pensionamientos.php', 'icono' => 'afiliacion'],
    ]],
    ['titulo' => 'RESTAURANTE', 'enlaces' => [
        ['texto' => 'Reservas del día', 'ruta' => '/admin/reservas.php', 'icono' => 'reservas'],
        ['texto' => 'Menú semanal', 'ruta' => '/admin/menus.php', 'icono' => 'menu'],
        ['texto' => 'Cocina y entregas', 'ruta' => '/admin/cocina.php', 'icono' => 'cocina'],
    ]],
    ['titulo' => 'CONTROL', 'enlaces' => [
        ['texto' => 'Reportes', 'ruta' => '/admin/reportes.php', 'icono' => 'reporte'],
        ['texto' => 'Parámetros', 'ruta' => '/admin/configuracion.php', 'icono' => 'configuracion'],
    ]],
];
$accionesDashboard = [
    ['texto' => 'Empleados', 'titulo' => 'Registrar empleado', 'detalle' => 'Crear una cuenta con CI, departamento y datos institucionales.', 'ruta' => '/admin/empleados.php', 'icono' => 'personas'],
    ['texto' => 'Familia', 'titulo' => 'Vincular familiar', 'detalle' => 'Registrar padres, hijos o cónyuge de una persona titular.', 'ruta' => '/admin/familiares.php', 'icono' => 'familia'],
    ['texto' => 'Afiliación', 'titulo' => 'Gestionar pensionamientos', 'detalle' => 'Asistir con la afiliación mensual y reservas automáticas.', 'ruta' => '/admin/pensionamientos.php', 'icono' => 'afiliacion'],
    ['texto' => 'Servicio', 'titulo' => 'Consultar reservas', 'detalle' => 'Revisar reservas, entregas pendientes e inasistencias.', 'ruta' => '/admin/reservas.php', 'icono' => 'reservas'],
    ['texto' => 'Planificación', 'titulo' => 'Publicar menú semanal', 'detalle' => 'Organizar sopa, plato principal y postre de cada jornada.', 'ruta' => '/admin/menus.php', 'icono' => 'menu'],
    ['texto' => 'Cocina', 'titulo' => 'Consolidado de cocina', 'detalle' => 'Consultar cantidades y registrar almuerzos entregados.', 'ruta' => '/admin/cocina.php', 'icono' => 'cocina'],
    ['texto' => 'Administración', 'titulo' => 'Reporte de planilla', 'detalle' => 'Generar y revisar los descuentos del periodo mensual.', 'ruta' => '/admin/reportes.php', 'icono' => 'reporte'],
    ['texto' => 'Configuración', 'titulo' => 'Parámetros y calendario', 'detalle' => 'Actualizar tarifas, mínimos, horarios y feriados.', 'ruta' => '/admin/configuracion.php', 'icono' => 'configuracion'],
];
$zonaBolivia = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
$diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaDashboard = ucfirst($diasSemana[(int) $zonaBolivia->format('w')]) . ', ' . $zonaBolivia->format('j') . ' de ' . $meses[(int) $zonaBolivia->format('n') - 1];
require __DIR__ . '/../_dashboard-start.php';
?>
<section class="dashboard-bienvenida">
    <div>
        <span class="dashboard-kicker">PANEL DE ADMINISTRACIÓN · UAB</span>
        <h2>Gestión del restaurante</h2>
        <p>Organiza el servicio diario, el equipo y la planificación desde un solo lugar.</p>
    </div>
    <div class="dashboard-fecha"><span>HOY</span><strong><?= escaparHtml($zonaBolivia->format('d')) ?></strong><small><?= escaparHtml($meses[(int) $zonaBolivia->format('n') - 1]) ?> · <?= escaparHtml($zonaBolivia->format('Y')) ?></small></div>
</section>
<div class="dashboard-seccion-cabecera">
    <div><span class="dashboard-kicker dashboard-kicker-azul">CENTRO DE OPERACIONES</span><h2>Accesos de administración</h2></div>
    <p><?= escaparHtml($fechaDashboard) ?></p>
</div>
<section class="dashboard-modulos" aria-label="Accesos de administración">
    <?php foreach ($accionesDashboard as $accion): ?>
        <a class="dashboard-modulo" href="<?= escaparHtml(rutaBasePublica() . $accion['ruta']) ?>">
            <span class="dashboard-modulo-icono" aria-hidden="true"><?= $iconosDashboard[$accion['icono']] ?></span>
            <span class="dashboard-modulo-etiqueta"><?= escaparHtml($accion['texto']) ?></span>
            <strong><?= escaparHtml($accion['titulo']) ?></strong>
            <span class="dashboard-modulo-detalle"><?= escaparHtml($accion['detalle']) ?></span>
            <span class="dashboard-modulo-ir" aria-hidden="true">→</span>
        </a>
    <?php endforeach; ?>
</section>
<?php require __DIR__ . '/../_dashboard-end.php'; ?>
