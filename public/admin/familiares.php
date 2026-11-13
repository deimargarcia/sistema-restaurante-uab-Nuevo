<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}

$base = rutaBasePublica();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vincular familiar | Restaurante Rumbo al Cielo</title>
    <link rel="stylesheet" href="<?= escaparHtml($base) ?>/assets/css/app.css">
</head>
<body class="pagina-panel">
<main class="panel">
    <header class="panel-cabecera">
        <a class="marca marca-enlace" href="<?= escaparHtml($base) ?>/admin/dashboard.php">
            <img src="<?= escaparHtml($base) ?>/assets/img/uab-favicon.png" alt="">
            <span><strong>Rumbo al Cielo</strong><small>ADMINISTRACIÓN</small></span>
        </a>
        <a class="boton-secundario" href="<?= escaparHtml($base) ?>/admin/dashboard.php">Volver al panel</a>
    </header>
    <section class="panel-contenido">
        <span class="etiqueta etiqueta-verde">FAMILIARES</span>
        <h1>Vincular familiar</h1>
        <p>Selecciona al empleado titular. Solo se admiten padres, cónyuge e hijos.</p>

        <div id="mensajeFamiliar" class="mensaje" role="status" aria-live="polite"></div>
        <form id="formFamiliar" class="formulario-vinculo">
            <label for="titular_id">Empleado titular</label>
            <select id="titular_id" name="titular_id" required disabled><option value="">Cargando empleados…</option></select>
            <label for="nombre">Nombre del familiar</label>
            <input id="nombre" name="nombre" maxlength="150" required>
            <label for="parentesco">Parentesco</label>
            <select id="parentesco" name="parentesco" required>
                <option value="">Selecciona una opción</option>
                <option value="PADRE">Padre</option>
                <option value="MADRE">Madre</option>
                <option value="CONYUGE">Cónyuge</option>
                <option value="HIJO">Hijo</option>
                <option value="HIJA">Hija</option>
            </select>
            <button id="guardarFamiliar" class="boton-formulario" type="submit" disabled>Vincular familiar</button>
        </form>
    </section>
</main>
<script>
const formulario = document.getElementById('formFamiliar');
const titular = document.getElementById('titular_id');
const mensaje = document.getElementById('mensajeFamiliar');
const boton = document.getElementById('guardarFamiliar');

function mostrarError(texto) {
    mensaje.textContent = texto;
    mensaje.className = 'mensaje mensaje-error';
}

async function cargarTitulares() {
    try {
        const respuesta = await fetch('<?= escaparHtml($base) ?>/api/familiares.php', { headers: { 'Accept': 'application/json' } });
        const datos = await respuesta.json();
        if (!respuesta.ok || !datos.exito) throw new Error(datos.mensaje || 'No se pudieron cargar los empleados.');

        titular.replaceChildren(new Option('Selecciona un empleado', ''));
        for (const empleado of datos.titulares) {
            const codigo = empleado.codigo_empleado ? ` · ${empleado.codigo_empleado}` : '';
            titular.add(new Option(`${empleado.nombre_completo}${codigo}`, empleado.id));
        }
        titular.disabled = datos.titulares.length === 0;
        boton.disabled = datos.titulares.length === 0;
        if (datos.titulares.length === 0) mostrarError('Primero registra un empleado para vincular a su familiar.');
    } catch (error) {
        mostrarError(error.message || 'No se pudieron cargar los empleados.');
    }
}

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    mensaje.textContent = '';
    mensaje.className = 'mensaje';
    boton.disabled = true;
    try {
        const respuesta = await fetch('<?= escaparHtml($base) ?>/api/familiares.php', {
            method: 'POST', body: new FormData(formulario), headers: { 'Accept': 'application/json' }
        });
        const datos = await respuesta.json();
        mensaje.textContent = datos.mensaje || 'No fue posible vincular al familiar.';
        mensaje.classList.add(respuesta.ok && datos.exito ? 'mensaje-exito' : 'mensaje-error');
        if (respuesta.ok && datos.exito) formulario.reset();
    } catch (error) {
        mostrarError('No se pudo conectar con el sistema. Intenta de nuevo.');
    } finally {
        boton.disabled = false;
    }
});

cargarTitulares();
</script>
</body>
</html>
