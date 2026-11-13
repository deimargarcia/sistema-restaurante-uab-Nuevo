<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();
if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}
$lunes = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->modify('monday this week')->format('Y-m-d');
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Menú semanal | Restaurante Rumbo al Cielo</title><link rel="stylesheet" href="<?= escaparHtml(rutaBasePublica()) ?>/assets/css/app.css"></head>
<body class="pagina-panel"><main class="panel">
    <header class="panel-cabecera"><a class="marca marca-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/dashboard.php"><img src="<?= escaparHtml(rutaBasePublica()) ?>/assets/img/uab-favicon.png" alt=""><span><strong>Rumbo al Cielo</strong><small>ADMINISTRACIÓN</small></span></a><a class="boton-secundario" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/dashboard.php">Volver al inicio</a></header>
    <section class="panel-contenido panel-contenido-ancho"><span class="etiqueta etiqueta-verde">PLANIFICACIÓN DE COCINA</span><h1>Menú semanal</h1><p>Completa los platos de lunes a jueves. Puedes publicar o actualizar cada día; las fechas feriadas no aceptan menú.</p>
        <div class="campo-formulario"><label for="semana">Semana de referencia</label><input id="semana" type="date" value="<?= escaparHtml($lunes) ?>"></div>
        <p class="mensaje" id="mensaje-menu" role="status" aria-live="polite"></p><div id="formularios-menu" class="menu-semanal-editor">Cargando semana…</div>
    </section>
</main>
<script>
const semana = document.querySelector('#semana');
const contenedor = document.querySelector('#formularios-menu');
const mensaje = document.querySelector('#mensaje-menu');
const nombres = ['Lunes', 'Martes', 'Miércoles', 'Jueves'];
const dia = 86400000;
function fechaTexto(fecha) { return fecha.toISOString().slice(0, 10); }

async function cargarSemana() {
    const seleccion = new Date(`${semana.value}T12:00:00`);
    const lunes = new Date(seleccion);
    const desplazamiento = (lunes.getDay() + 6) % 7;
    lunes.setDate(lunes.getDate() - desplazamiento);
    const inicio = fechaTexto(lunes);
    const respuesta = await fetch(`../api/menus.php?fecha=${encodeURIComponent(inicio)}`);
    const resultado = await respuesta.json();
    const existentes = new Map((resultado.menus ?? []).map((menu) => [menu.fecha, menu]));
    contenedor.replaceChildren();
    for (let indice = 0; indice < 4; indice++) {
        const fecha = new Date(lunes.getTime() + indice * dia);
        const clave = fechaTexto(fecha);
        const menu = existentes.get(clave) ?? {};
        const tarjeta = document.createElement('form');
        tarjeta.className = 'panel-tarjeta formulario-menu-dia';
        tarjeta.dataset.fecha = clave;
        const titulo = document.createElement('h2');
        titulo.textContent = `${nombres[indice]} · ${fecha.toLocaleDateString('es-BO')}`;
        tarjeta.append(titulo);
        for (const [campo, texto, valor] of [['sopa','Sopa',menu.sopa ?? ''],['plato_principal','Plato principal',menu.plato_principal ?? ''],['postre','Postre',menu.postre ?? '']]) {
            const label = document.createElement('label'); label.textContent = texto; label.htmlFor = `${campo}-${clave}`;
            const input = document.createElement('input'); input.id = label.htmlFor; input.name = campo; input.value = valor; input.maxLength = 150; input.required = true;
            tarjeta.append(label, input);
        }
        const guardar = document.createElement('button'); guardar.type = 'submit'; guardar.className = 'boton-formulario'; guardar.textContent = 'Publicar menú';
        tarjeta.append(guardar);
        tarjeta.addEventListener('submit', async (evento) => {
            evento.preventDefault(); guardar.disabled = true;
            const datos = new FormData(tarjeta); datos.set('fecha', clave);
            try {
                const respuestaGuardar = await fetch('../api/menus.php', {method:'POST',body:datos});
                const resultadoGuardar = await respuestaGuardar.json();
                mensaje.textContent = resultadoGuardar.mensaje;
                mensaje.className = `mensaje ${resultadoGuardar.exito ? 'mensaje-exito' : 'mensaje-error'}`;
            } catch { mensaje.textContent = 'No se pudo guardar el menú.'; mensaje.className = 'mensaje mensaje-error'; }
            finally { guardar.disabled = false; }
        });
        contenedor.append(tarjeta);
    }
}
semana.addEventListener('change', () => cargarSemana().catch(() => { contenedor.textContent = 'No se pudo cargar la semana.'; }));
cargarSemana().catch(() => { contenedor.textContent = 'No se pudo cargar la semana.'; });
</script>
</body></html>
