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
$claseDashboard = 'pagina-dashboard-admin-inicio';
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
        ['texto' => 'Horario y feriados', 'ruta' => '/admin/configuracion.php', 'icono' => 'configuracion'],
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
    ['texto' => 'Reglas de servicio', 'titulo' => 'Horario, mínimo y feriados', 'detalle' => 'Ajustar el horario límite, el mínimo mensual y el calendario operativo.', 'ruta' => '/admin/configuracion.php', 'icono' => 'configuracion'],
];
$zonaBolivia = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
$diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaDashboard = ucfirst($diasSemana[(int) $zonaBolivia->format('w')]) . ', ' . $zonaBolivia->format('j') . ' de ' . $meses[(int) $zonaBolivia->format('n') - 1];
require __DIR__ . '/../_dashboard-start.php';
?>
<div class="admin-inicio-layout">
    <section class="admin-inicio-principal">
        <div class="admin-inicio-saludo">
            <span class="dashboard-kicker dashboard-kicker-azul">PANEL DE ADMINISTRACIÓN · UAB</span>
            <h2>Buen día, <?= escaparHtml(explode(' ', $nombreDashboard)[0]) ?>.</h2>
            <p><?= escaparHtml($fechaDashboard) ?> · Organiza el servicio del restaurante.</p>
        </div>
        <article class="admin-accion-prioritaria">
            <span class="admin-accion-icono" aria-hidden="true"><?= $iconosDashboard['menu'] ?></span>
            <span class="dashboard-kicker">PLANIFICACIÓN DE COCINA</span>
            <h2>Publicar menú semanal</h2>
            <p>Completa la sopa, el plato principal y el postre de lunes a jueves. Añade una foto para que los empleados vean el menú antes de reservar.</p>
            <a class="admin-accion-boton" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/menus.php">Abrir planificación <span aria-hidden="true">→</span></a>
        </article>
    </section>
    <aside class="admin-modulos-lateral" aria-labelledby="tituloModulosAdmin">
        <div class="admin-modulos-cabecera">
            <span class="dashboard-kicker dashboard-kicker-azul">GESTIÓN DEL RESTAURANTE</span>
            <h2 id="tituloModulosAdmin">Módulos</h2>
        </div>
        <nav class="admin-modulos-lista" aria-label="Módulos de administración">
            <?php foreach ($accionesDashboard as $accion): ?>
                <?php if ($accion['icono'] === 'menu') continue; ?>
                <a class="admin-modulo-enlace" href="<?= escaparHtml(rutaBasePublica() . $accion['ruta']) ?>">
                    <span class="admin-modulo-icono" aria-hidden="true"><?= $iconosDashboard[$accion['icono']] ?></span>
                    <span><strong><?= escaparHtml($accion['titulo']) ?></strong><small><?= escaparHtml($accion['texto']) ?></small></span>
                    <span class="admin-modulo-flecha" aria-hidden="true">›</span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
</div>
<?php require __DIR__ . '/../_dashboard-end.php'; ?>
