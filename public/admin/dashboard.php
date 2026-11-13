<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}

$nombre = escaparHtml((string) ($_SESSION['nombre'] ?? 'Administración'));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administración | Restaurante Rumbo al Cielo</title>
    <link rel="stylesheet" href="<?= escaparHtml(rutaBasePublica()) ?>/assets/css/app.css">
</head>
<body class="pagina-panel">
<main class="panel">
    <header class="panel-cabecera">
        <div class="marca">
            <img src="<?= escaparHtml(rutaBasePublica()) ?>/assets/img/uab-favicon.png" alt="">
            <span><strong>Rumbo al Cielo</strong><small>ADMINISTRACIÓN</small></span>
        </div>
        <form action="<?= escaparHtml(rutaBasePublica()) ?>/logout.php" method="post">
            <button class="boton-secundario" type="submit">Cerrar sesión</button>
        </form>
    </header>
    <section class="panel-contenido">
        <span class="etiqueta etiqueta-verde">PANEL DE ADMINISTRACIÓN</span>
        <h1>Hola, <?= $nombre ?></h1>
        <p>La sesión está activa con permisos de administración.</p>
        <a class="panel-tarjeta panel-tarjeta-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/empleados.php"><strong>Registrar empleado</strong><span>Agregar a una persona de la nómina y crear su cuenta.</span></a>
        <a class="panel-tarjeta panel-tarjeta-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/familiares.php"><strong>Vincular familiar</strong><span>Asociar padres, cónyuge o hijos a un empleado titular.</span></a>
        <a class="panel-tarjeta panel-tarjeta-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/pensionamientos.php"><strong>Gestionar afiliaciones</strong><span>Asistir a titulares pensionados con su afiliación mensual y reservas.</span></a>
        <a class="panel-tarjeta panel-tarjeta-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/reservas.php"><strong>Consultar reservas del día</strong><span>Revisar reservas, entregas pendientes e inasistencias cobradas.</span></a>
        <a class="panel-tarjeta panel-tarjeta-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/menus.php"><strong>Publicar menú semanal</strong><span>Registrar sopa, plato principal y postre para cada día operativo.</span></a>
        <a class="panel-tarjeta panel-tarjeta-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/cocina.php"><strong>Consolidado de cocina</strong><span>Consultar el total del día y registrar los almuerzos entregados.</span></a>
        <a class="panel-tarjeta panel-tarjeta-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/reportes.php"><strong>Reporte de planilla</strong><span>Generar y revisar los descuentos mensuales por titular y familiares.</span></a>
        <a class="panel-tarjeta panel-tarjeta-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/configuracion.php"><strong>Parámetros y calendario</strong><span>Ajustar tarifas, mínimo mensual, horario límite y feriados.</span></a>
    </section>
</main>
</body>
</html>
