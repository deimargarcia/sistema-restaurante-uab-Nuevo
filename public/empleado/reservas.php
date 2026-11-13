<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['tipo_cuenta'] ?? '', ['PENSIONADO', 'NO_PENSIONADO'], true)) {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}

$hoy = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->format('Y-m-d');
$esPensionado = ($_SESSION['tipo_cuenta'] ?? '') === 'PENSIONADO';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservas | Restaurante Rumbo al Cielo</title>
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
    <section class="panel-contenido panel-contenido-ancho">
        <span class="etiqueta etiqueta-verde">RESERVAS DE ALMUERZO</span>
        <h1>Reservar un almuerzo</h1>
        <p>Reserva para un día operativo con menú publicado. Las reservas del mismo día se reciben hasta las 9:00. No se aceptan domingos, viernes, sábados ni feriados.</p>
        <form class="formulario-empleado" id="formulario-reserva">
            <?php if ($esPensionado): ?>
                <div class="campo-formulario">
                    <label for="beneficiario">Beneficiario</label>
                    <select id="beneficiario" name="familiar_id"><option value="">Yo</option></select>
                </div>
            <?php endif; ?>
            <div class="campo-formulario">
                <label for="fecha">Fecha del almuerzo</label>
                <input id="fecha" name="fecha" type="date" min="<?= escaparHtml($hoy) ?>" required>
            </div>
            <button class="boton-formulario" id="enviar-reserva" type="submit">Confirmar reserva</button>
        </form>
        <p class="mensaje" id="mensaje-reserva" role="status" aria-live="polite"></p>
        <h2>Mis reservas</h2>
        <div id="lista-reservas" class="lista-reservas" aria-live="polite">Cargando…</div>
    </section>
</main>
<script>
const formulario = document.querySelector('#formulario-reserva');
const beneficiario = document.querySelector('#beneficiario');
const mensaje = document.querySelector('#mensaje-reserva');
const lista = document.querySelector('#lista-reservas');
const boton = document.querySelector('#enviar-reserva');
const nombresEstado = {
    RESERVADO: 'Reservado',
    CANCELADO: 'Cancelado',
    PENDIENTE_ENTREGA: 'Pendiente de entrega',
    ENTREGADO: 'Entregado',
    COBRADO_INASISTENCIA: 'Inasistencia cobrada'
};

function avisar(texto, correcto) {
    mensaje.textContent = texto;
    mensaje.className = `mensaje ${correcto ? 'mensaje-exito' : 'mensaje-error'}`;
}

async function cargarReservas() {
    const respuesta = await fetch('../api/reservas.php');
    const resultado = await respuesta.json();
    if (!resultado.exito) throw new Error(resultado.mensaje);
    if (resultado.reservas.length === 0) {
        lista.textContent = 'Todavía no tienes reservas registradas.';
        return;
    }
    const tabla = document.createElement('table');
    tabla.className = 'tabla-reservas';
    const encabezado = document.createElement('tr');
    for (const titulo of ['Fecha', 'Beneficiario', 'Estado', 'Modalidad', 'Acción']) {
        const celda = document.createElement('th');
        celda.textContent = titulo;
        encabezado.append(celda);
    }
    const head = document.createElement('thead');
    head.append(encabezado);
    tabla.append(head);
    const cuerpo = document.createElement('tbody');
    for (const reserva of resultado.reservas) {
        const fila = document.createElement('tr');
        const valores = [reserva.fecha_reserva, reserva.tipo_beneficiario === 'FAMILIAR' ? reserva.nombre_familiar : 'Titular', nombresEstado[reserva.estado] ?? reserva.estado, reserva.modalidad === 'PENSIONADO' ? 'Mensual' : 'Esporádica'];
        for (const valor of valores) {
            const celda = document.createElement('td');
            celda.textContent = valor ?? '';
            fila.append(celda);
        }
        const accion = document.createElement('td');
        if (reserva.estado === 'RESERVADO') {
            const cancelar = document.createElement('button');
            cancelar.type = 'button';
            cancelar.className = 'boton-secundario';
            cancelar.textContent = 'Cancelar';
            cancelar.addEventListener('click', () => cancelarReserva(reserva.id));
            accion.append(cancelar);
        } else {
            accion.textContent = '—';
        }
        fila.append(accion);
        cuerpo.append(fila);
    }
    tabla.append(cuerpo);
    lista.replaceChildren(tabla);
}

async function cancelarReserva(reservaId) {
    if (!window.confirm('¿Quieres cancelar esta reserva?')) return;
    const datos = new FormData();
    datos.set('accion', 'cancelar');
    datos.set('reserva_id', reservaId);
    const respuesta = await fetch('../api/reservas.php', { method: 'POST', body: datos });
    const resultado = await respuesta.json();
    avisar(resultado.mensaje, resultado.exito);
    if (resultado.exito) await cargarReservas();
}

if (beneficiario) {
    fetch('../api/pensionamientos.php')
        .then((respuesta) => respuesta.json())
        .then((resultado) => {
            if (!resultado.exito) return;
            for (const familiar of resultado.familiares) beneficiario.add(new Option(`${familiar.nombre} · ${familiar.parentesco}`, familiar.id));
        })
        .catch(() => avisar('No se pudieron cargar tus familiares.', false));
}

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    boton.disabled = true;
    try {
        const respuesta = await fetch('../api/reservas.php', { method: 'POST', body: new FormData(formulario) });
        const resultado = await respuesta.json();
        const precio = resultado.tarifa ? ` Tarifa: Bs ${Number(resultado.tarifa).toFixed(2)}.` : '';
        avisar(resultado.mensaje + (resultado.exito ? precio : ''), resultado.exito);
        if (resultado.exito) {
            formulario.reset();
            document.querySelector('#fecha').min = '<?= escaparHtml($hoy) ?>';
            await cargarReservas();
        }
    } catch {
        avisar('No se pudo contactar al sistema. Inténtalo de nuevo.', false);
    } finally {
        boton.disabled = false;
    }
});

cargarReservas().catch(() => { lista.textContent = 'No se pudieron cargar las reservas.'; });
</script>
</body>
</html>
