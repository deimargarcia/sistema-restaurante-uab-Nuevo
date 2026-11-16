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
    <title>Usuarios | Restaurante Rumbo al Cielo</title>
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
                <span class="etiqueta etiqueta-verde">PERSONAS Y ACCESOS</span>
                <h1>Usuarios</h1>
                <p>Consulta la nómina, actualiza los datos y habilita o suspende cuentas.</p>
            </div>
            <a class="boton-formulario boton-enlace" href="<?= escaparHtml($base) ?>/admin/registrar-empleado.php">Registrar empleado <span aria-hidden="true">＋</span></a>
        </div>

        <div class="usuarios-herramientas">
            <label for="buscarUsuario">Buscar por nombre, CI o correo</label>
            <input id="buscarUsuario" type="search" placeholder="Ej. Ana, 1234567 o ana@uab.bo" autocomplete="off">
            <label class="filtro-estado" for="filtroEstado">Estado</label>
            <select id="filtroEstado"><option value="TODOS">Todos</option><option value="ACTIVO">Activos</option><option value="INACTIVO">Inactivos</option></select>
        </div>

        <div id="mensajeUsuarios" class="mensaje" role="status" aria-live="polite"></div>
        <div class="lista-reservas tabla-usuarios-contenedor">
            <table class="tabla-reservas tabla-usuarios">
                <thead><tr><th>Empleado</th><th>CI</th><th>Departamento</th><th>Cuenta</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody id="filasUsuarios"><tr><td colspan="6">Cargando usuarios...</td></tr></tbody>
            </table>
        </div>
        <p class="nota-usuarios">Las cuentas suspendidas conservan sus reservas y registros históricos, pero ya no pueden iniciar sesión.</p>
    </section>
</main>

<dialog id="dialogoEditarUsuario" class="dialogo-usuario">
    <form id="formEditarUsuario" class="dialogo-usuario-contenido">
        <div class="dialogo-usuario-cabecera"><div><span class="etiqueta">NÓMINA INSTITUCIONAL</span><h2>Editar empleado</h2></div><button class="boton-cerrar-dialogo" type="button" id="cerrarDialogo" aria-label="Cerrar">×</button></div>
        <input type="hidden" name="id" id="editarId">
        <div class="formulario-empleado formulario-editar-usuario">
            <div class="campo-formulario"><label for="editarNombre">Nombre</label><input id="editarNombre" name="nombre" maxlength="150" required></div>
            <div class="campo-formulario"><label for="editarApellido">Apellido</label><input id="editarApellido" name="apellido" maxlength="100" required></div>
            <div class="campo-formulario"><label for="editarCorreo">Correo</label><input id="editarCorreo" name="correo" type="email" maxlength="150" required></div>
            <div class="campo-formulario"><label for="editarCelular">Teléfono</label><input id="editarCelular" name="celular" type="tel" maxlength="20" required></div>
            <div class="campo-formulario"><label for="editarCi">CI</label><input id="editarCi" name="ci" maxlength="20" required></div>
            <div class="campo-formulario"><label for="editarDepartamento">Departamento</label><input id="editarDepartamento" name="departamento" maxlength="100" required></div>
            <div class="campo-formulario"><label for="editarCodigo">Código de empleado</label><input id="editarCodigo" name="codigo_empleado" maxlength="50" required></div>
            <div class="campo-formulario"><label for="editarTipo">Tipo de cuenta</label><select id="editarTipo" name="tipo_cuenta" required><option value="PENSIONADO">Pensionado</option><option value="NO_PENSIONADO">No pensionado</option></select></div>
            <div class="campo-formulario campo-contrasena"><label for="editarContrasena">Nueva contraseña <span>(opcional)</span></label><input id="editarContrasena" name="contrasena" type="password" minlength="6" autocomplete="new-password" placeholder="Dejar en blanco para conservarla"></div>
        </div>
        <div id="mensajeEditarUsuario" class="mensaje" role="status" aria-live="polite"></div>
        <div class="dialogo-usuario-acciones"><button class="boton-secundario" type="button" id="cancelarEdicion">Cancelar</button><button class="boton-formulario" id="guardarEdicion" type="submit">Guardar cambios</button></div>
    </form>
</dialog>

<script>
const apiUsuarios = '<?= escaparHtml($base) ?>/api/gestion-empleados.php';
const filasUsuarios = document.getElementById('filasUsuarios');
const mensajeUsuarios = document.getElementById('mensajeUsuarios');
const buscarUsuario = document.getElementById('buscarUsuario');
const filtroEstado = document.getElementById('filtroEstado');
const dialogo = document.getElementById('dialogoEditarUsuario');
const formularioEditar = document.getElementById('formEditarUsuario');
const mensajeEditar = document.getElementById('mensajeEditarUsuario');
let usuarios = [];

function mostrarMensaje(elemento, texto, exito) {
    elemento.textContent = texto;
    elemento.className = 'mensaje ' + (exito ? 'mensaje-exito' : 'mensaje-error');
}

async function enviar(datos) {
    const respuesta = await fetch(apiUsuarios, { method: 'POST', body: datos, headers: { 'Accept': 'application/json' } });
    const cuerpo = await respuesta.json();
    if (!respuesta.ok || !cuerpo.exito) throw new Error(cuerpo.mensaje || 'No se pudo completar la operación.');
    return cuerpo;
}

function celda(fila, texto) {
    const td = document.createElement('td');
    td.textContent = texto || '—';
    fila.append(td);
    return td;
}

function dibujarUsuarios() {
    const busqueda = buscarUsuario.value.trim().toLocaleLowerCase('es');
    const estado = filtroEstado.value;
    const visibles = usuarios.filter((usuario) => {
        const coincideTexto = [usuario.nombre, usuario.apellido, usuario.ci, usuario.correo]
            .join(' ').toLocaleLowerCase('es').includes(busqueda);
        const coincideEstado = estado === 'TODOS' || (estado === 'ACTIVO' ? usuario.activo : !usuario.activo);
        return coincideTexto && coincideEstado;
    });
    filasUsuarios.replaceChildren();
    if (!visibles.length) {
        const fila = document.createElement('tr');
        const celdaVacia = celda(fila, 'No hay usuarios que coincidan con la búsqueda.');
        celdaVacia.colSpan = 6;
        filasUsuarios.append(fila);
        return;
    }
    for (const usuario of visibles) {
        const fila = document.createElement('tr');
        const persona = document.createElement('td');
        const nombre = document.createElement('strong');
        nombre.textContent = `${usuario.nombre} ${usuario.apellido}`.trim();
        const correo = document.createElement('small');
        correo.textContent = usuario.correo;
        persona.append(nombre, correo);
        fila.append(persona);
        celda(fila, usuario.ci);
        celda(fila, usuario.departamento);
        celda(fila, usuario.tipo_cuenta === 'PENSIONADO' ? 'Pensionado' : 'No pensionado');
        const celdaEstado = celda(fila, usuario.activo ? 'Activo' : 'Suspendido');
        celdaEstado.classList.add('celda-estado', usuario.activo ? 'estado-activo' : 'estado-inactivo');
        const acciones = document.createElement('td');
        acciones.className = 'acciones-usuario';
        const editar = document.createElement('button');
        editar.type = 'button'; editar.className = 'accion-tabla'; editar.textContent = 'Editar';
        editar.addEventListener('click', () => abrirEdicion(usuario));
        const estadoBtn = document.createElement('button');
        estadoBtn.type = 'button'; estadoBtn.className = usuario.activo ? 'accion-tabla accion-peligro' : 'accion-tabla';
        estadoBtn.textContent = usuario.activo ? 'Suspender' : 'Reactivar';
        estadoBtn.addEventListener('click', () => cambiarEstado(usuario));
        acciones.append(editar, estadoBtn);
        fila.append(acciones);
        filasUsuarios.append(fila);
    }
}

async function cargarUsuarios() {
    filasUsuarios.innerHTML = '<tr><td colspan="6">Cargando usuarios...</td></tr>';
    try {
        const respuesta = await fetch(apiUsuarios, { headers: { 'Accept': 'application/json' } });
        const datos = await respuesta.json();
        if (!respuesta.ok || !datos.exito) throw new Error(datos.mensaje || 'No se pudo cargar la nómina.');
        usuarios = datos.empleados;
        dibujarUsuarios();
    } catch (error) {
        filasUsuarios.innerHTML = '<tr><td colspan="6">No se pudo cargar la lista. Actualiza la página para volver a intentar.</td></tr>';
        mostrarMensaje(mensajeUsuarios, error.message, false);
    }
}

function abrirEdicion(usuario) {
    formularioEditar.reset();
    mensajeEditar.textContent = ''; mensajeEditar.className = 'mensaje';
    for (const campo of ['id', 'nombre', 'apellido', 'correo', 'celular', 'ci', 'departamento']) {
        document.getElementById('editar' + campo[0].toUpperCase() + campo.slice(1)).value = usuario[campo];
    }
    document.getElementById('editarCodigo').value = usuario.codigo_empleado || '';
    document.getElementById('editarTipo').value = usuario.tipo_cuenta;
    dialogo.showModal();
}

async function cambiarEstado(usuario) {
    const accion = usuario.activo ? 'suspender' : 'reactivar';
    if (!window.confirm(`¿Deseas ${accion} la cuenta de ${usuario.nombre} ${usuario.apellido}?`)) return;
    const datos = new FormData();
    datos.set('accion', 'estado'); datos.set('id', usuario.id); datos.set('activo', usuario.activo ? '0' : '1');
    try {
        const resultado = await enviar(datos);
        mostrarMensaje(mensajeUsuarios, resultado.mensaje, true);
        await cargarUsuarios();
    } catch (error) {
        mostrarMensaje(mensajeUsuarios, error.message, false);
    }
}

formularioEditar.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    const datos = new FormData(formularioEditar);
    datos.set('accion', 'actualizar');
    const boton = document.getElementById('guardarEdicion');
    boton.disabled = true;
    try {
        const resultado = await enviar(datos);
        dialogo.close();
        mostrarMensaje(mensajeUsuarios, resultado.mensaje, true);
        await cargarUsuarios();
    } catch (error) {
        mostrarMensaje(mensajeEditar, error.message, false);
    } finally {
        boton.disabled = false;
    }
});

document.getElementById('cerrarDialogo').addEventListener('click', () => dialogo.close());
document.getElementById('cancelarEdicion').addEventListener('click', () => dialogo.close());
buscarUsuario.addEventListener('input', dibujarUsuarios);
filtroEstado.addEventListener('change', dibujarUsuarios);
cargarUsuarios();
</script>
</body>
</html>
