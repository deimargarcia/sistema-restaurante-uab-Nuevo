<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

$tipoCuentaActual = (string) ($_SESSION['tipo_cuenta'] ?? '');
$rolesEmpleado = ['PENSIONADO', 'NO_PENSIONADO'];
if (empty($_SESSION['usuario_id']) || !in_array($tipoCuentaActual, $rolesEmpleado, true)) {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}

$nombreDashboard = trim((string) ($_SESSION['nombre'] ?? 'Empleado/a'));
$tipoDashboard = $tipoCuentaActual;
$rolDashboard = 'PORTAL DEL EMPLEADO';
$subtituloDashboard = $tipoCuentaActual === 'PENSIONADO' ? 'Empleado pensionado' : 'Empleado no pensionado';
$tituloDashboard = 'Mi espacio';
$gruposNavegacion = [
    ['titulo' => 'MI RESTAURANTE', 'enlaces' => [
        ['texto' => 'Inicio', 'ruta' => '/empleado/dashboard.php', 'icono' => 'inicio', 'activo' => true],
        ['texto' => 'Menú semanal', 'ruta' => '/empleado/menu.php', 'icono' => 'menu'],
        ['texto' => 'Mis reservas', 'ruta' => '/empleado/reservas.php', 'icono' => 'reservas'],
        ['texto' => 'Afiliación mensual', 'ruta' => '/empleado/pensionamiento.php', 'icono' => 'afiliacion'],
    ]],
];
$nombreCorto = explode(' ', $nombreDashboard)[0] ?: 'Empleado/a';
$nombreCorto = escaparHtml($nombreCorto);
$accionesDashboard = [
    ['texto' => 'PLANIFICA TU SEMANA', 'titulo' => 'Consulta el menú', 'detalle' => 'Revisa la sopa, el plato principal y el postre publicados para cada día.', 'ruta' => '/empleado/menu.php', 'icono' => 'menu', 'clase' => 'modulo-menu'],
    ['texto' => 'ORGANIZA TUS ALMUERZOS', 'titulo' => 'Gestiona tus reservas', 'detalle' => 'Reserva para ti o para un familiar afiliado y revisa tus solicitudes.', 'ruta' => '/empleado/reservas.php', 'icono' => 'reservas', 'clase' => 'modulo-reservas'],
    ['texto' => 'SERVICIO MENSUAL', 'titulo' => 'Afiliación mensual', 'detalle' => 'Solicita el servicio del siguiente mes y programa los almuerzos hábiles.', 'ruta' => '/empleado/pensionamiento.php', 'icono' => 'afiliacion', 'clase' => 'modulo-afiliacion'],
];
$zonaBolivia = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
$diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaDashboard = ucfirst($diasSemana[(int) $zonaBolivia->format('w')]) . ', ' . $zonaBolivia->format('j') . ' de ' . $meses[(int) $zonaBolivia->format('n') - 1];
require __DIR__ . '/../_dashboard-start.php';
?>
<section class="dashboard-bienvenida dashboard-bienvenida-empleado">
    <div>
        <span class="dashboard-kicker">TU ESPACIO · RESTAURANTE UAB</span>
        <h2>Hola, <?= $nombreCorto ?>.</h2>
        <p>Consulta el menú de la semana y organiza tus próximos almuerzos.</p>
        <a class="dashboard-cta" href="<?= escaparHtml(rutaBasePublica()) ?>/empleado/reservas.php"><span aria-hidden="true">＋</span> Hacer una reserva</a>
    </div>
    <div class="dashboard-fecha"><span>HOY</span><strong><?= escaparHtml($zonaBolivia->format('d')) ?></strong><small><?= escaparHtml($meses[(int) $zonaBolivia->format('n') - 1]) ?> · <?= escaparHtml($zonaBolivia->format('Y')) ?></small></div>
</section>
<div class="dashboard-seccion-cabecera">
    <div><span class="dashboard-kicker dashboard-kicker-azul">SERVICIOS DISPONIBLES</span><h2>¿Qué necesitas hacer?</h2></div>
    <p><?= escaparHtml($fechaDashboard) ?></p>
</div>
<section class="dashboard-modulos dashboard-modulos-empleado" aria-label="Servicios para empleados">
    <?php foreach ($accionesDashboard as $accion): ?>
        <a class="dashboard-modulo <?= escaparHtml($accion['clase']) ?>" href="<?= escaparHtml(rutaBasePublica() . $accion['ruta']) ?>">
            <span class="dashboard-modulo-icono" aria-hidden="true"><?= $iconosDashboard[$accion['icono']] ?></span>
            <span class="dashboard-modulo-etiqueta"><?= escaparHtml($accion['texto']) ?></span>
            <strong><?= escaparHtml($accion['titulo']) ?></strong>
            <span class="dashboard-modulo-detalle"><?= escaparHtml($accion['detalle']) ?></span>
            <span class="dashboard-modulo-ir" aria-hidden="true">→</span>
        </a>
    <?php endforeach; ?>
</section>
<aside class="dashboard-nota" aria-label="Información del servicio">
    <span class="dashboard-nota-icono" aria-hidden="true">i</span>
    <p><strong>Servicio de almuerzo UAB</strong><br>Las reservas y afiliaciones se rigen por el horario, los días operativos y las tarifas configuradas por el restaurante.</p>
</aside>
<?php require __DIR__ . '/../_dashboard-end.php'; ?>
