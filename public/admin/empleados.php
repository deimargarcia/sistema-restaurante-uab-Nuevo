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
    <title>Registrar empleado | Restaurante Rumbo al Cielo</title>
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
        <span class="etiqueta etiqueta-verde">NÓMINA INSTITUCIONAL</span>
        <h1>Registrar empleado</h1>
        <p>Completa los datos de la persona para crear su cuenta de acceso.</p>

        <div id="mensajeEmpleado" class="mensaje" role="status" aria-live="polite"></div>
        <form id="formEmpleado" class="formulario-empleado">
            <div class="campo-formulario"><label for="nombre">Nombre</label><input id="nombre" name="nombre" maxlength="150" required></div>
            <div class="campo-formulario"><label for="apellido">Apellido</label><input id="apellido" name="apellido" maxlength="100" required></div>
            <div class="campo-formulario"><label for="correo">Correo</label><input id="correo" name="correo" type="email" maxlength="150" required></div>
            <div class="campo-formulario"><label for="contrasena">Contraseña inicial</label><input id="contrasena" name="contrasena" type="password" minlength="6" autocomplete="new-password" required></div>
            <div class="campo-formulario"><label for="celular">Teléfono</label><input id="celular" name="celular" type="tel" maxlength="20" required></div>
            <div class="campo-formulario"><label for="ci">CI</label><input id="ci" name="ci" maxlength="20" required></div>
            <div class="campo-formulario"><label for="departamento">Departamento</label><input id="departamento" name="departamento" maxlength="100" required></div>
            <div class="campo-formulario"><label for="codigo_empleado">Código de empleado</label><input id="codigo_empleado" name="codigo_empleado" maxlength="50" required></div>
            <div class="campo-formulario"><label for="tipo_cuenta">Tipo de cuenta</label><select id="tipo_cuenta" name="tipo_cuenta" required><option value="">Selecciona una opción</option><option value="PENSIONADO">Pensionado</option><option value="NO_PENSIONADO">No pensionado</option></select></div>
            <button id="guardarEmpleado" class="boton-formulario" type="submit">Registrar empleado</button>
        </form>
    </section>
</main>
<script>
const formulario = document.getElementById('formEmpleado');
const mensaje = document.getElementById('mensajeEmpleado');
const boton = document.getElementById('guardarEmpleado');

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    mensaje.textContent = '';
    mensaje.className = 'mensaje';
    boton.disabled = true;

    try {
        const respuesta = await fetch('<?= escaparHtml($base) ?>/api/empleados.php', {
            method: 'POST',
            body: new FormData(formulario),
            headers: { 'Accept': 'application/json' }
        });
        const datos = await respuesta.json();
        mensaje.textContent = datos.mensaje || 'No fue posible completar el registro.';
        mensaje.classList.add(respuesta.ok && datos.exito ? 'mensaje-exito' : 'mensaje-error');
        if (respuesta.ok && datos.exito) formulario.reset();
    } catch (error) {
        mensaje.textContent = 'No se pudo conectar con el sistema. Intenta de nuevo.';
        mensaje.classList.add('mensaje-error');
    } finally {
        boton.disabled = false;
    }
});
</script>
</body>
</html>
