<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

iniciarSesionWeb();
$base = rutaBasePublica();

if (!empty($_SESSION['usuario_id'])) {
    $panel = $_SESSION['tipo_cuenta'] === 'ADMIN'
        ? '/admin/dashboard.php'
        : '/empleado/dashboard.php';
    header('Location: ' . $base . $panel);
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#15392c">
    <title>Ingresar | Restaurante Rumbo al Cielo</title>
    <link rel="stylesheet" href="<?= escaparHtml($base) ?>/assets/css/app.css">
</head>
<body class="pagina-acceso">
<main class="acceso-marco">
    <section class="presentacion" aria-label="Restaurante Rumbo al Cielo">
        <div class="marca">
            <img src="<?= escaparHtml($base) ?>/assets/img/uab-favicon.png" alt="">
            <span><strong>Rumbo al Cielo</strong><small>RESTAURANTE · UAB</small></span>
        </div>
        <div class="presentacion-texto">
            <span class="etiqueta">SERVICIO DE ALMUERZOS</span>
            <h1>Tu almuerzo,<br><em>bien organizado.</em></h1>
            <p>Consulta el menú y gestiona tus reservas desde un solo lugar.</p>
        </div>
        <div class="dato-restaurante"><span>UNIVERSIDAD ADVENTISTA DE BOLIVIA</span><span>VINTO · COCHABAMBA</span></div>
    </section>

    <section class="formulario-zona" aria-labelledby="tituloAcceso">
        <div class="formulario-acceso">
            <div class="marca marca-movil">
                <img src="<?= escaparHtml($base) ?>/assets/img/uab-favicon.png" alt="">
                <span><strong>Rumbo al Cielo</strong><small>RESTAURANTE · UAB</small></span>
            </div>
            <span class="etiqueta etiqueta-verde">ACCESO AL SISTEMA</span>
            <h2 id="tituloAcceso">Bienvenido/a</h2>
            <p class="ayuda">Ingresa con tu correo institucional o número de CI.</p>

            <div id="mensajeAcceso" class="mensaje" role="status" aria-live="polite"></div>

            <form id="formAcceso" autocomplete="on">
                <label for="correo_o_ci">Correo o CI</label>
                <input id="correo_o_ci" name="correo_o_ci" type="text" required autocomplete="username" placeholder="nombre@uab.bo o tu CI">

                <label for="contrasena">Contraseña</label>
                <input id="contrasena" name="contrasena" type="password" required autocomplete="current-password" placeholder="Escribe tu contraseña">

                <button id="botonAcceso" type="submit">Ingresar <span aria-hidden="true">→</span></button>
            </form>
            <p class="nota-acceso">Acceso reservado al personal autorizado de la institución.</p>
        </div>
    </section>
</main>
<script>
const baseProyecto = <?= json_encode($base, JSON_UNESCAPED_SLASHES) ?>;
const formulario = document.getElementById('formAcceso');
const boton = document.getElementById('botonAcceso');
const mensaje = document.getElementById('mensajeAcceso');

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    mensaje.textContent = '';
    mensaje.className = 'mensaje';
    boton.disabled = true;
    boton.firstChild.textContent = 'Ingresando… ';

    try {
        const respuesta = await fetch(`${baseProyecto}/api/acceso.php`, {
            method: 'POST',
            body: new FormData(formulario),
            headers: { 'Accept': 'application/json' }
        });
        const datos = await respuesta.json();

        if (!respuesta.ok || !datos.exito) {
            mensaje.textContent = datos.mensaje || 'No fue posible iniciar sesión.';
            mensaje.classList.add('mensaje-error');
            return;
        }

        window.location.assign(datos.destino);
    } catch (error) {
        mensaje.textContent = 'No se pudo conectar con el sistema. Intenta de nuevo.';
        mensaje.classList.add('mensaje-error');
    } finally {
        boton.disabled = false;
        boton.firstChild.textContent = 'Ingresar ';
    }
});
</script>
</body>
</html>
