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
        <div class="panel-tarjeta"><strong>Acceso confirmado</strong><span>Los módulos de gestión se irán incorporando según las historias de usuario.</span></div>
    </section>
</main>
</body>
</html>
