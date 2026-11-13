<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}
$hoy = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->format('Y-m-d');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservas del día | Restaurante Rumbo al Cielo</title>
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
    <section class="panel-contenido panel-contenido-ancho">
        <span class="etiqueta etiqueta-verde">CONTROL DE RESERVAS</span>
        <h1>Reservas del día</h1>
        <p>El estado se actualiza automáticamente: las reservas sin cancelar pasan a pendientes de entrega después del corte, y las que quedaron sin atender pasan a inasistencia cobrada al terminar el día.</p>
        <form class="formulario-empleado" id="filtro-reservas">
            <div class="campo-formulario"><label for="fecha">Fecha de servicio</label><input id="fecha" type="date" value="<?= escaparHtml($hoy) ?>" required></div>
            <button class="boton-formulario" type="submit">Consultar</button>
        </form>
        <p class="mensaje" id="mensaje" role="status" aria-live="polite"></p>
        <div id="lista-reservas" class="lista-reservas">Cargando…</div>
    </section>
</main>
<script>
const filtro = document.querySelector('#filtro-reservas');
const fecha = document.querySelector('#fecha');
const lista = document.querySelector('#lista-reservas');
const mensaje = document.querySelector('#mensaje');
const estados = { RESERVADO: 'Reservado', CANCELADO: 'Cancelado', PENDIENTE_ENTREGA: 'Pendiente de entrega', ENTREGADO: 'Entregado', COBRADO_INASISTENCIA: 'Inasistencia cobrada' };

async function consultar() {
    const respuesta = await fetch(`../api/reservas.php?fecha=${encodeURIComponent(fecha.value)}`);
    const resultado = await respuesta.json();
    if (!resultado.exito) throw new Error(resultado.mensaje);
    if (resultado.reservas.length === 0) {
        lista.textContent = 'No hay reservas para la fecha seleccionada.';
        return;
    }
    const tabla = document.createElement('table');
    tabla.className = 'tabla-reservas';
    const titulos = ['Titular', 'Código', 'Beneficiario', 'Estado', 'Modalidad'];
    const thead = document.createElement('thead');
    const filaCabecera = document.createElement('tr');
    for (const titulo of titulos) { const celda = document.createElement('th'); celda.textContent = titulo; filaCabecera.append(celda); }
    thead.append(filaCabecera);
    const tbody = document.createElement('tbody');
    for (const reserva of resultado.reservas) {
        const fila = document.createElement('tr');
        const datos = [reserva.nombre_titular, reserva.codigo_empleado, reserva.tipo_beneficiario === 'FAMILIAR' ? reserva.nombre_familiar : 'Titular', estados[reserva.estado] ?? reserva.estado, reserva.modalidad === 'PENSIONADO' ? 'Mensual' : 'Esporádica'];
        for (const dato of datos) { const celda = document.createElement('td'); celda.textContent = dato ?? ''; fila.append(celda); }
        tbody.append(fila);
    }
    tabla.append(thead, tbody);
    lista.replaceChildren(tabla);
}

filtro.addEventListener('submit', (evento) => {
    evento.preventDefault();
    mensaje.textContent = '';
    consultar().catch((error) => { mensaje.textContent = error.message; mensaje.className = 'mensaje mensaje-error'; });
});
consultar().catch((error) => { lista.textContent = error.message; });
</script>
</body>
</html>
