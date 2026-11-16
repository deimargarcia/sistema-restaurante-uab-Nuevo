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
        ['texto' => 'Usuarios', 'ruta' => '/admin/empleados.php', 'icono' => 'personas'],
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
</div>
<?php require __DIR__ . '/../_dashboard-end.php'; ?>
