<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();
if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['tipo_cuenta'] ?? '', ['PENSIONADO', 'NO_PENSIONADO'], true)) {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}
$lunes = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->modify('monday this week')->format('Y-m-d');
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Menú semanal | Restaurante Rumbo al Cielo</title><link rel="stylesheet" href="<?= escaparHtml(rutaBasePublica()) ?>/assets/css/app.css"></head>
<body class="pagina-panel"><main class="panel">
    <header class="panel-cabecera"><a class="marca marca-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/empleado/dashboard.php"><img src="<?= escaparHtml(rutaBasePublica()) ?>/assets/img/uab-favicon.png" alt=""><span><strong>Rumbo al Cielo</strong><small>PORTAL DEL EMPLEADO</small></span></a><a class="boton-secundario" href="<?= escaparHtml(rutaBasePublica()) ?>/empleado/dashboard.php">Volver al inicio</a></header>
    <section class="panel-contenido panel-contenido-ancho"><span class="etiqueta etiqueta-verde">MENÚ INSTITUCIONAL</span><h1>Menú de la semana</h1><p>Consulta lo que se servirá de lunes a jueves.</p>
        <div class="campo-formulario"><label for="semana">Semana de referencia</label><input id="semana" type="date" value="<?= escaparHtml($lunes) ?>"></div>
        <div id="menus-semana" class="menu-semanal-editor">Cargando menú…</div>
    </section>
</main>
<script>
const campoSemana = document.querySelector('#semana');
const panelMenus = document.querySelector('#menus-semana');
function fechaLunes(value) { const date = new Date(`${value}T12:00:00`); date.setDate(date.getDate() - ((date.getDay() + 6) % 7)); return date.toISOString().slice(0,10); }
async function cargar() {
    const inicio = fechaLunes(campoSemana.value);
    const response = await fetch(`../api/menus.php?fecha=${encodeURIComponent(inicio)}`);
    const data = await response.json();
    panelMenus.replaceChildren();
    if (!data.menus?.length) { panelMenus.textContent = 'Todavía no se publicaron menús para esta semana.'; return; }
    for (const menu of data.menus) {
        const tarjeta = document.createElement('article'); tarjeta.className = 'panel-tarjeta menu-dia';
        const titulo = document.createElement('h2'); titulo.textContent = new Date(`${menu.fecha}T12:00:00`).toLocaleDateString('es-BO',{weekday:'long',day:'numeric',month:'long'});
        tarjeta.append(titulo);
        for (const [rotulo, valor] of [['Sopa',menu.sopa],['Plato principal',menu.plato_principal],['Postre',menu.postre]]) {
            const parrafo = document.createElement('p'); const strong=document.createElement('strong'); strong.textContent=`${rotulo}: `; parrafo.append(strong,document.createTextNode(valor)); tarjeta.append(parrafo);
        }
        if (menu.imagen_path) { const imagen=document.createElement('img'); imagen.src=menu.imagen_path; imagen.alt=`Menú del ${menu.fecha}`; imagen.loading='lazy'; imagen.className='menu-imagen'; tarjeta.prepend(imagen); }
        panelMenus.append(tarjeta);
    }
}
campoSemana.addEventListener('change',()=>cargar().catch(()=>{panelMenus.textContent='No se pudo cargar el menú.';}));
cargar().catch(()=>{panelMenus.textContent='No se pudo cargar el menú.';});
</script>
</body></html>
