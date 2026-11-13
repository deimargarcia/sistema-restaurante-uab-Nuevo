<?php
declare(strict_types=1);

$baseDashboard = escaparHtml(rutaBasePublica());
$inicialDashboard = mb_strtoupper(mb_substr($nombreDashboard, 0, 1, 'UTF-8'), 'UTF-8');
$iconosDashboard = [
    'inicio' => '<svg viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1z"/></svg>',
    'personas' => '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-7.5a4 4 0 0 1 0 7m1 3a4 4 0 0 1 3 4v1"/></svg>',
    'familia' => '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1m2-9a3 3 0 1 0-2-5m2 10a5 5 0 0 1 4 5"/></svg>',
    'afiliacion' => '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h8m-8 3h5"/></svg>',
    'reservas' => '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18m-13 4h.01M12 14h.01M16 14h.01M8 18h.01"/></svg>',
    'menu' => '<svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>',
    'cocina' => '<svg viewBox="0 0 24 24"><path d="M3 11h18l-2 9H5l-2-9Zm3 0a6 6 0 0 1 12 0M8 15h.01M12 15h.01M16 15h.01"/></svg>',
    'reporte' => '<svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6"/></svg>',
    'configuracion' => '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.7 1.7 0 1 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.3a1.7 1.7 0 1 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 1 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9h-.3a1.7 1.7 0 1 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 1 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2v-.3a1.7 1.7 0 1 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 1 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.3a1.7 1.7 0 1 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z"/></svg>',
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#123b68">
    <title><?= escaparHtml($tituloDashboard) ?> | Restaurante Rumbo al Cielo</title>
    <link rel="stylesheet" href="<?= $baseDashboard ?>/assets/css/app.css">
    <script src="<?= $baseDashboard ?>/assets/js/dashboard.js" defer></script>
</head>
<body class="pagina-dashboard">
<div class="dashboard-overlay" id="dashboardOverlay"></div>
<aside class="dashboard-sidebar" id="dashboardSidebar" aria-label="Navegación principal">
    <a class="dashboard-brand" href="<?= $baseDashboard ?>/<?= $tipoDashboard === 'ADMIN' ? 'admin' : 'empleado' ?>/dashboard.php">
        <img src="<?= $baseDashboard ?>/assets/img/uab-favicon.png" alt="">
        <span><strong>Rumbo al Cielo</strong><small>RESTAURANTE · UAB</small></span>
    </a>
    <nav class="dashboard-nav">
        <?php foreach ($gruposNavegacion as $grupo): ?>
            <span class="dashboard-nav-titulo"><?= escaparHtml($grupo['titulo']) ?></span>
            <?php foreach ($grupo['enlaces'] as $enlace): ?>
                <a class="dashboard-nav-enlace<?= !empty($enlace['activo']) ? ' activo' : '' ?>" href="<?= $baseDashboard . escaparHtml($enlace['ruta']) ?>"<?= !empty($enlace['activo']) ? ' aria-current="page"' : '' ?>>
                    <span class="dashboard-nav-icono" aria-hidden="true"><?= $iconosDashboard[$enlace['icono']] ?></span>
                    <span><?= escaparHtml($enlace['texto']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <div class="dashboard-sidebar-pie">
        <span class="dashboard-estado"><i aria-hidden="true"></i> Portal institucional</span>
        <div class="dashboard-usuario">
            <span class="dashboard-avatar"><?= escaparHtml($inicialDashboard) ?></span>
            <span><strong><?= escaparHtml($nombreDashboard) ?></strong><small><?= escaparHtml($rolDashboard) ?></small></span>
        </div>
    </div>
</aside>
<div class="dashboard-principal">
    <header class="dashboard-topbar">
        <div class="dashboard-topbar-inicio">
            <button class="dashboard-menu-boton" id="dashboardMenuBoton" type="button" aria-label="Abrir menú" aria-controls="dashboardSidebar" aria-expanded="false">☰</button>
            <div><span class="dashboard-miga">RUMBO AL CIELO <i>/</i> <?= escaparHtml($rolDashboard) ?></span><h1><?= escaparHtml($tituloDashboard) ?></h1></div>
        </div>
        <div class="dashboard-topbar-acciones">
            <div class="dashboard-identidad"><span class="dashboard-avatar"><?= escaparHtml($inicialDashboard) ?></span><span><strong><?= escaparHtml($nombreDashboard) ?></strong><small><?= escaparHtml($subtituloDashboard) ?></small></span></div>
            <form action="<?= $baseDashboard ?>/logout.php" method="post"><button class="dashboard-salir" type="submit"><span aria-hidden="true">↪</span> Cerrar sesión</button></form>
        </div>
    </header>
    <main class="dashboard-main">
