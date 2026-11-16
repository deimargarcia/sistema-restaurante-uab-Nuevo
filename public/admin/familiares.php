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
    <title>Familiares | Restaurante Rumbo al Cielo</title>
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
    <section class="panel-contenido panel-contenido-ancho">
        <div class="gestion-usuarios-cabecera">
            <div>
                <span class="etiqueta etiqueta-verde">PERSONAS Y VÍNCULOS</span>
                <h1>Familiares</h1>
                <p>Registra y actualiza familiares con el parentesco permitido y su empleado titular.</p>
            </div>
        </div>

        <section class="panel-tarjeta tarjeta-registro-familiar" aria-labelledby="tituloRegistroFamiliar">
            <div><strong id="tituloRegistroFamiliar">Registrar familiar</strong><span>Se vinculará al empleado seleccionado.</span></div>
            <form id="formFamiliar" class="formulario-registro-familiar">
                <div class="campo-formulario"><label for="titular_id">Empleado titular</label><select id="titular_id" name="titular_id" required disabled><option value="">Cargando empleados…</option></select></div>
                <div class="campo-formulario"><label for="nombre">Nombre completo</label><input id="nombre" name="nombre" maxlength="150" required></div>
                <div class="campo-formulario"><label for="ci">CI del familiar</label><input id="ci" name="ci" maxlength="20" required></div>
                <div class="campo-formulario"><label for="parentesco">Parentesco</label><select id="parentesco" name="parentesco" required><option value="">Selecciona</option><option value="PADRE">Padre</option><option value="MADRE">Madre</option><option value="CONYUGE">Cónyuge</option><option value="HIJO">Hijo</option><option value="HIJA">Hija</option></select></div>
                <button id="guardarFamiliar" class="boton-formulario" type="submit" disabled>Registrar familiar</button>
            </form>
        </section>

        <div class="familiares-herramientas">
            <label for="buscarFamiliar">Buscar por familiar, CI o empleado</label>
            <input id="buscarFamiliar" type="search" placeholder="Escribe un nombre o CI" autocomplete="off">
            <label class="filtro-estado" for="filtroFamiliar">Estado</label>
            <select id="filtroFamiliar"><option value="TODOS">Todos</option><option value="ACTIVO">Activos</option><option value="INACTIVO">Inactivos</option></select>
        </div>
        <div id="mensajeFamiliares" class="mensaje" role="status" aria-live="polite"></div>
        <div class="lista-reservas tabla-usuarios-contenedor">
            <table class="tabla-reservas tabla-familiares">
                <thead><tr><th>Familiar</th><th>CI</th><th>Parentesco</th><th>Empleado titular</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="filasFamiliares"><tr><td colspan="6">Cargando familiares...</td></tr></tbody>
            </table>
        </div>
        <p class="nota-usuarios">Al desactivar un familiar se conserva su historial y se impiden nuevas reservas.</p>
    </section>
</main>

<dialog id="dialogoEditarFamiliar" class="dialogo-usuario">
    <form id="formEditarFamiliar" class="dialogo-usuario-contenido">
        <div class="dialogo-usuario-cabecera"><div><span class="etiqueta">VÍNCULO FAMILIAR</span><h2>Editar familiar</h2></div><button class="boton-cerrar-dialogo" id="cerrarEdicionFamiliar" type="button" aria-label="Cerrar">×</button></div>
        <input type="hidden" name="id" id="editarFamiliarId">
        <div class="formulario-vinculo">
            <label for="editarFamiliarNombre">Nombre completo</label><input id="editarFamiliarNombre" name="nombre" maxlength="150" required>
            <label for="editarFamiliarCi">CI</label><input id="editarFamiliarCi" name="ci" maxlength="20" required>
            <label for="editarFamiliarParentesco">Parentesco</label><select id="editarFamiliarParentesco" name="parentesco" required><option value="PADRE">Padre</option><option value="MADRE">Madre</option><option value="CONYUGE">Cónyuge</option><option value="HIJO">Hijo</option><option value="HIJA">Hija</option></select>
        </div>
        <div id="mensajeEditarFamiliar" class="mensaje" role="status" aria-live="polite"></div>
        <div class="dialogo-usuario-acciones"><button class="boton-secundario" id="cancelarEdicionFamiliar" type="button">Cancelar</button><button class="boton-formulario" id="guardarEdicionFamiliar" type="submit">Guardar cambios</button></div>
    </form>
</dialog>

<script>
const apiFamiliares = '<?= escaparHtml($base) ?>/api/familiares.php';
const formRegistro = document.getElementById('formFamiliar');
const selectorTitular = document.getElementById('titular_id');
const botonRegistro = document.getElementById('guardarFamiliar');
const filas = document.getElementById('filasFamiliares');
const mensaje = document.getElementById('mensajeFamiliares');
const busqueda = document.getElementById('buscarFamiliar');
const filtro = document.getElementById('filtroFamiliar');
const dialogo = document.getElementById('dialogoEditarFamiliar');
const formEdicion = document.getElementById('formEditarFamiliar');
const mensajeEdicion = document.getElementById('mensajeEditarFamiliar');
let familiares = [];

function avisar(elemento, texto, exito) {
    elemento.textContent = texto;
    elemento.className = 'mensaje ' + (exito ? 'mensaje-exito' : 'mensaje-error');
}

function agregarCelda(fila, texto) {
    const celda = document.createElement('td'); celda.textContent = texto || '—'; fila.append(celda); return celda;
}

function renderizar() {
    const consulta = busqueda.value.trim().toLocaleLowerCase('es');
    const estado = filtro.value;
    const lista = familiares.filter((f) => {
        const coincide = [f.nombre, f.ci, f.parentesco, f.titular_nombre, f.codigo_empleado].join(' ').toLocaleLowerCase('es').includes(consulta);
        return coincide && (estado === 'TODOS' || f.estado === estado);
    });
    filas.replaceChildren();
    if (!lista.length) {
        const fila = document.createElement('tr'); const celda = agregarCelda(fila, 'No hay familiares que coincidan con la búsqueda.'); celda.colSpan = 6; filas.append(fila); return;
    }
    for (const familiar of lista) {
        const fila = document.createElement('tr');
        agregarCelda(fila, familiar.nombre);
        agregarCelda(fila, familiar.ci);
        const parentesco = familiar.parentesco || 'SIN DEFINIR';
        agregarCelda(fila, parentesco === 'CONYUGE' ? 'Cónyuge' : parentesco[0] + parentesco.slice(1).toLowerCase());
        const titular = agregarCelda(fila, `${familiar.titular_nombre}${familiar.codigo_empleado ? ' · ' + familiar.codigo_empleado : ''}`);
        titular.classList.add('celda-titular-familiar');
        const estadoCelda = agregarCelda(fila, familiar.estado === 'ACTIVO' ? 'Activo' : 'Inactivo');
        estadoCelda.classList.add('celda-estado', familiar.estado === 'ACTIVO' ? 'estado-activo' : 'estado-inactivo');
        const acciones = document.createElement('td'); acciones.className = 'acciones-usuario';
        const editar = document.createElement('button'); editar.type = 'button'; editar.className = 'accion-tabla'; editar.textContent = 'Editar';
        editar.addEventListener('click', () => abrirEdicion(familiar));
        const estadoBtn = document.createElement('button'); estadoBtn.type = 'button'; estadoBtn.className = familiar.estado === 'ACTIVO' ? 'accion-tabla accion-peligro' : 'accion-tabla';
        estadoBtn.textContent = familiar.estado === 'ACTIVO' ? 'Desactivar' : 'Reactivar';
        estadoBtn.addEventListener('click', () => cambiarEstado(familiar));
        acciones.append(editar, estadoBtn); fila.append(acciones); filas.append(fila);
    }
}

async function cargar() {
    try {
        const respuesta = await fetch(apiFamiliares, { headers: { 'Accept': 'application/json' } });
        const datos = await respuesta.json();
        if (!respuesta.ok || !datos.exito) throw new Error(datos.mensaje || 'No se pudieron cargar los familiares.');
        selectorTitular.replaceChildren(new Option('Selecciona un empleado', ''));
        for (const titular of datos.titulares) selectorTitular.add(new Option(`${titular.nombre_completo}${titular.codigo_empleado ? ' · ' + titular.codigo_empleado : ''}`, titular.id));
        selectorTitular.disabled = datos.titulares.length === 0; botonRegistro.disabled = datos.titulares.length === 0;
        familiares = datos.familiares || []; renderizar();
        if (!datos.titulares.length) avisar(mensaje, 'Primero registra un empleado activo para vincular a su familiar.', false);
    } catch (error) {
        filas.innerHTML = '<tr><td colspan="6">No se pudo cargar la lista. Actualiza la página para volver a intentar.</td></tr>';
        avisar(mensaje, error.message, false);
    }
}

async function enviar(datos) {
    const respuesta = await fetch(apiFamiliares, { method: 'POST', body: datos, headers: { 'Accept': 'application/json' } });
    const resultado = await respuesta.json();
    if (!respuesta.ok || !resultado.exito) throw new Error(resultado.mensaje || 'No se pudo completar la operación.');
    return resultado;
}

function abrirEdicion(familiar) {
    formEdicion.reset(); mensajeEdicion.textContent = ''; mensajeEdicion.className = 'mensaje';
    document.getElementById('editarFamiliarId').value = familiar.id;
    document.getElementById('editarFamiliarNombre').value = familiar.nombre;
    document.getElementById('editarFamiliarCi').value = familiar.ci;
    document.getElementById('editarFamiliarParentesco').value = familiar.parentesco;
    dialogo.showModal();
}

async function cambiarEstado(familiar) {
    const nuevoEstado = familiar.estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
    const verbo = nuevoEstado === 'INACTIVO' ? 'desactivar' : 'reactivar';
    if (!window.confirm(`¿Deseas ${verbo} a ${familiar.nombre}?`)) return;
    const datos = new FormData(); datos.set('accion', 'estado'); datos.set('id', familiar.id); datos.set('estado', nuevoEstado);
    try { const r = await enviar(datos); avisar(mensaje, r.mensaje, true); await cargar(); }
    catch (error) { avisar(mensaje, error.message, false); }
}

formRegistro.addEventListener('submit', async (evento) => {
    evento.preventDefault(); botonRegistro.disabled = true;
    const datos = new FormData(formRegistro); datos.set('accion', 'registrar');
    try { const r = await enviar(datos); avisar(mensaje, r.mensaje, true); formRegistro.reset(); await cargar(); }
    catch (error) { avisar(mensaje, error.message, false); }
    finally { botonRegistro.disabled = selectorTitular.disabled; }
});

formEdicion.addEventListener('submit', async (evento) => {
    evento.preventDefault(); const boton = document.getElementById('guardarEdicionFamiliar'); boton.disabled = true;
    const datos = new FormData(formEdicion); datos.set('accion', 'actualizar');
    try { const r = await enviar(datos); dialogo.close(); avisar(mensaje, r.mensaje, true); await cargar(); }
    catch (error) { avisar(mensajeEdicion, error.message, false); }
    finally { boton.disabled = false; }
});

document.getElementById('cerrarEdicionFamiliar').addEventListener('click', () => dialogo.close());
document.getElementById('cancelarEdicionFamiliar').addEventListener('click', () => dialogo.close());
busqueda.addEventListener('input', renderizar); filtro.addEventListener('change', renderizar);
cargar();
</script>
</body>
</html>
