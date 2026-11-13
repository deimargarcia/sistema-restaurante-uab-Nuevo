<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['tipo_cuenta'] ?? '', ['PENSIONADO', 'NO_PENSIONADO'], true)) {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}

$ahora = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
$proximoMes = $ahora->modify('first day of next month')->format('Y-m');
$nombre = escaparHtml((string) ($_SESSION['nombre'] ?? 'Empleado/a'));
$esPensionado = ($_SESSION['tipo_cuenta'] ?? '') === 'PENSIONADO';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Afiliación mensual | Restaurante Rumbo al Cielo</title>
    <link rel="stylesheet" href="<?= escaparHtml(rutaBasePublica()) ?>/assets/css/app.css">
</head>
<body class="pagina-panel">
<main class="panel">
    <header class="panel-cabecera">
        <a class="marca marca-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/empleado/dashboard.php">
            <img src="<?= escaparHtml(rutaBasePublica()) ?>/assets/img/uab-favicon.png" alt="">
            <span><strong>Rumbo al Cielo</strong><small>PORTAL DEL EMPLEADO</small></span>
        </a>
        <a class="boton-secundario" href="<?= escaparHtml(rutaBasePublica()) ?>/empleado/dashboard.php">Volver al inicio</a>
    </header>
    <section class="panel-contenido">
        <span class="etiqueta etiqueta-verde">SERVICIO MENSUAL</span>
        <h1>Afiliación mensual</h1>
        <p>Hola, <?= $nombre ?>. Elige un mes que aún no haya comenzado. La afiliación genera reservas automáticas de lunes a jueves; los feriados quedan excluidos.</p>
        <div class="panel-tarjeta">
            <strong>Bs 15 por almuerzo</strong>
            <span>Las reservas quedan registradas para todo el periodo y se aplican las reglas vigentes del restaurante.</span>
        </div>
        <form class="formulario-vinculo" id="formulario-afiliacion">
            <?php if ($esPensionado): ?>
                <label for="beneficiario">¿A quién deseas afiliar?</label>
                <select id="beneficiario" name="familiar_id">
                    <option value="">A mí</option>
                </select>
            <?php endif; ?>
            <label for="periodo">Mes de afiliación</label>
            <input id="periodo" name="periodo" type="month" min="<?= escaparHtml($proximoMes) ?>" value="<?= escaparHtml($proximoMes) ?>" required>
            <button class="boton-formulario" id="enviar-afiliacion" type="submit">Afiliarme y crear reservas</button>
        </form>
        <p class="mensaje" id="mensaje-afiliacion" role="status" aria-live="polite"></p>
    </section>
</main>
<script>
const formulario = document.querySelector('#formulario-afiliacion');
const mensaje = document.querySelector('#mensaje-afiliacion');
const boton = document.querySelector('#enviar-afiliacion');
const beneficiario = document.querySelector('#beneficiario');

if (beneficiario) {
    fetch('../api/pensionamientos.php', { headers: { 'Accept': 'application/json' } })
        .then((respuesta) => respuesta.json())
        .then((resultado) => {
            if (!resultado.exito) return;
            for (const familiar of resultado.familiares) {
                const opcion = document.createElement('option');
                opcion.value = familiar.id;
                opcion.textContent = `${familiar.nombre} · ${familiar.parentesco}`;
                beneficiario.append(opcion);
            }
        })
        .catch(() => {
            mensaje.textContent = 'No se pudieron cargar tus familiares. Puedes afiliarte a ti mismo.';
            mensaje.classList.add('mensaje-error');
        });
}

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    mensaje.className = 'mensaje';
    mensaje.textContent = '';
    boton.disabled = true;
    boton.textContent = 'Procesando…';

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
        boton.textContent = 'Afiliarme y crear reservas';
    }
});
</script>
</body>
</html>
