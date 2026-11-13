<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

$rolesEmpleado = ['PENSIONADO', 'NO_PENSIONADO'];
if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['tipo_cuenta'] ?? '', $rolesEmpleado, true)) {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}

$nombre = escaparHtml((string) ($_SESSION['nombre'] ?? 'Empleado/a'));
$tipoCuenta = escaparHtml((string) $_SESSION['tipo_cuenta']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Empleado | Restaurante Rumbo al Cielo</title>
    <link rel="stylesheet" href="<?= escaparHtml(rutaBasePublica()) ?>/assets/css/app.css">
</head>
<body class="pagina-panel">
<main class="panel">
    <header class="panel-cabecera">
        <div class="marca">
            <img src="<?= escaparHtml(rutaBasePublica()) ?>/assets/img/uab-favicon.png" alt="">
            <span><strong>Rumbo al Cielo</strong><small>PORTAL DEL EMPLEADO</small></span>
        </div>
        <form action="<?= escaparHtml(rutaBasePublica()) ?>/logout.php" method="post">
            <button class="boton-secundario" type="submit">Cerrar sesión</button>
        </form>
    </header>
    <section class="panel-contenido">
        <span class="etiqueta etiqueta-verde">PORTAL DEL EMPLEADO · <?= $tipoCuenta ?></span>
        <h1>Hola, <?= $nombre ?></h1>
        <p>Tu sesión está activa. Aquí aparecerán el menú y tus reservas.</p>
        <div class="panel-tarjeta"><strong>Bienvenido/a al restaurante</strong><span>Las funciones de menú, pensión y reservas se incorporarán siguiendo el backlog.</span></div>
    </section>
</main>
</body>
</html>
