<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}

$proximoMes = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->modify('first day of next month')->format('Y-m');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Afiliaciones | Restaurante Rumbo al Cielo</title>
    <link rel="stylesheet" href="<?= escaparHtml(rutaBasePublica()) ?>/assets/css/app.css">
</head>
<body class="pagina-panel">
<main class="panel">
    <header class="panel-cabecera">
        <a class="marca marca-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/dashboard.php">
            <img src="<?= escaparHtml(rutaBasePublica()) ?>/assets/img/uab-favicon.png" alt="">
            <span><strong>Rumbo al Cielo</strong><small>ADMINISTRACIÓN</small></span>
        </a>
        <a class="boton-secundario" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/dashboard.php">Volver al inicio</a>
    </header>
    <section class="panel-contenido">
        <span class="etiqueta etiqueta-verde">ASISTENCIA ADMINISTRATIVA</span>
        <h1>Gestionar afiliaciones</h1>
        <p>Selecciona un titular pensionado y registra su afiliación o la de un familiar activo. Solo se permiten periodos que todavía no hayan comenzado.</p>
        <div class="panel-tarjeta"><strong>Bs 15 por almuerzo</strong><span>El sistema programa los almuerzos de lunes a jueves y excluye los feriados del calendario.</span></div>
        <form class="formulario-vinculo" id="formulario-afiliacion">
            <label for="titular">Titular pensionado</label>
            <select id="titular" name="titular_id" required><option value="">Cargando titulares…</option></select>
            <label for="beneficiario">Beneficiario</label>
            <select id="beneficiario" name="familiar_id"><option value="">Titular</option></select>
            <label for="periodo">Mes de afiliación</label>
            <input id="periodo" name="periodo" type="month" min="<?= escaparHtml($proximoMes) ?>" value="<?= escaparHtml($proximoMes) ?>" required>
            <button class="boton-formulario" id="enviar-afiliacion" type="submit">Registrar afiliación y reservas</button>
        </form>
        <p class="mensaje" id="mensaje-afiliacion" role="status" aria-live="polite"></p>
    </section>
</main>
<script>
const formulario = document.querySelector('#formulario-afiliacion');
const titular = document.querySelector('#titular');
const beneficiario = document.querySelector('#beneficiario');
const mensaje = document.querySelector('#mensaje-afiliacion');
const boton = document.querySelector('#enviar-afiliacion');

async function cargarFamiliares() {
    beneficiario.replaceChildren(new Option('Titular', ''));
    if (!titular.value) return;
    const respuesta = await fetch(`../api/pensionamientos.php?titular_id=${encodeURIComponent(titular.value)}`);
    const resultado = await respuesta.json();
    if (!resultado.exito) throw new Error(resultado.mensaje);
    for (const familiar of resultado.familiares) {
        beneficiario.add(new Option(`${familiar.nombre} · ${familiar.parentesco}`, familiar.id));
    }
}

fetch('../api/pensionamientos.php')
    .then((respuesta) => respuesta.json())
    .then(async (resultado) => {
        titular.replaceChildren(new Option('Selecciona un titular', ''));
        for (const persona of resultado.titulares ?? []) {
            titular.add(new Option(`${persona.nombre_completo} · ${persona.codigo_empleado ?? 'Sin código'}`, persona.id));
        }
        if ((resultado.titulares ?? []).length === 0) titular.add(new Option('No hay titulares pensionados activos', ''));
    })
    .catch(() => {
        titular.replaceChildren(new Option('No se pudieron cargar los titulares', ''));
    });

titular.addEventListener('change', () => cargarFamiliares().catch((error) => {
    mensaje.textContent = error.message;
    mensaje.className = 'mensaje mensaje-error';
}));

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    mensaje.className = 'mensaje';
    mensaje.textContent = '';
    boton.disabled = true;
    try {
        const respuesta = await fetch('../api/pensionamientos.php', { method: 'POST', body: new FormData(formulario) });
        const resultado = await respuesta.json();
        mensaje.textContent = resultado.exito
            ? `${resultado.mensaje} Reservas generadas: ${resultado.reservas_creadas}.`
            : resultado.mensaje;
        mensaje.classList.add(resultado.exito ? 'mensaje-exito' : 'mensaje-error');
    } catch {
        mensaje.textContent = 'No se pudo contactar al sistema. Inténtalo de nuevo.';
        mensaje.classList.add('mensaje-error');
    } finally {
        boton.disabled = false;
    }
});
</script>
</body>
</html>
