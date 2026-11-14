<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
iniciarSesionWeb();

$tipoCuentaActual = (string) ($_SESSION['tipo_cuenta'] ?? '');
$rolesEmpleado = ['PENSIONADO', 'NO_PENSIONADO'];
if (empty($_SESSION['usuario_id']) || !in_array($tipoCuentaActual, $rolesEmpleado, true)) {
    header('Location: ' . rutaBasePublica() . '/index.php');
    exit;
}

$nombreDashboard = trim((string) ($_SESSION['nombre'] ?? 'Empleado/a'));
$tipoDashboard = $tipoCuentaActual;
$rolDashboard = 'PORTAL DEL EMPLEADO';
$subtituloDashboard = $tipoCuentaActual === 'PENSIONADO' ? 'Empleado pensionado' : 'Empleado no pensionado';
$tituloDashboard = 'Mi espacio';
$gruposNavegacion = [
    ['titulo' => 'MI RESTAURANTE', 'enlaces' => [
        ['texto' => 'Inicio', 'ruta' => '/empleado/dashboard.php', 'icono' => 'inicio', 'activo' => true],
        ['texto' => 'Menú semanal', 'ruta' => '/empleado/menu.php', 'icono' => 'menu'],
        ['texto' => 'Mis reservas', 'ruta' => '/empleado/reservas.php', 'icono' => 'reservas'],
        ['texto' => 'Afiliación mensual', 'ruta' => '/empleado/pensionamiento.php', 'icono' => 'afiliacion'],
    ]],
];
$nombreCorto = explode(' ', $nombreDashboard)[0] ?: 'Empleado/a';
$nombreCorto = escaparHtml($nombreCorto);
$inicioSemana = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))->modify('monday this week')->format('Y-m-d');
$accionesDashboard = [
    ['texto' => 'PLANIFICA TU SEMANA', 'titulo' => 'Consulta el menú', 'detalle' => 'Revisa la sopa, el plato principal y el postre publicados para cada día.', 'ruta' => '/empleado/menu.php', 'icono' => 'menu', 'clase' => 'modulo-menu'],
    ['texto' => 'ORGANIZA TUS ALMUERZOS', 'titulo' => 'Gestiona tus reservas', 'detalle' => 'Reserva para ti o para un familiar afiliado y revisa tus solicitudes.', 'ruta' => '/empleado/reservas.php', 'icono' => 'reservas', 'clase' => 'modulo-reservas'],
    ['texto' => 'SERVICIO MENSUAL', 'titulo' => 'Afiliación mensual', 'detalle' => 'Solicita el servicio del siguiente mes y programa los almuerzos hábiles.', 'ruta' => '/empleado/pensionamiento.php', 'icono' => 'afiliacion', 'clase' => 'modulo-afiliacion'],
];
$zonaBolivia = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
$diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaDashboard = ucfirst($diasSemana[(int) $zonaBolivia->format('w')]) . ', ' . $zonaBolivia->format('j') . ' de ' . $meses[(int) $zonaBolivia->format('n') - 1];
require __DIR__ . '/../_dashboard-start.php';
?>
<section class="dashboard-bienvenida dashboard-bienvenida-empleado">
    <div>
        <span class="dashboard-kicker">TU ESPACIO · RESTAURANTE UAB</span>
        <h2>Hola, <?= $nombreCorto ?>.</h2>
        <p>Consulta el menú de la semana y organiza tus próximos almuerzos.</p>
        <a class="dashboard-cta" href="<?= escaparHtml(rutaBasePublica()) ?>/empleado/reservas.php"><span aria-hidden="true">＋</span> Hacer una reserva</a>
    </div>
    <div class="dashboard-fecha"><span>HOY</span><strong><?= escaparHtml($zonaBolivia->format('d')) ?></strong><small><?= escaparHtml($meses[(int) $zonaBolivia->format('n') - 1]) ?> · <?= escaparHtml($zonaBolivia->format('Y')) ?></small></div>
</section>
<section class="menu-semanal-dashboard" aria-labelledby="tituloMenuSemana">
    <div class="menu-semanal-cabecera">
        <div><span class="dashboard-kicker dashboard-kicker-azul">DE LUNES A JUEVES</span><h2 id="tituloMenuSemana">Menú de la semana</h2><p>Elige tu almuerzo y reserva tu lugar.</p></div>
        <a class="menu-semanal-ver" href="<?= escaparHtml(rutaBasePublica()) ?>/empleado/menu.php">Ver semana completa <span aria-hidden="true">→</span></a>
    </div>
    <div id="menuSemanaEmpleado" class="menu-semanal-tarjetas" aria-live="polite" aria-busy="true"><p class="menu-semanal-cargando">Cargando el menú de esta semana…</p></div>
</section>
<div class="dashboard-seccion-cabecera">
    <div><span class="dashboard-kicker dashboard-kicker-azul">TUS SERVICIOS</span><h2>¿Qué necesitas hacer?</h2></div>
    <p><?= escaparHtml($fechaDashboard) ?></p>
</div>
<section class="dashboard-modulos dashboard-modulos-empleado" aria-label="Servicios para empleados">
    <?php foreach ($accionesDashboard as $accion): ?>
        <a class="dashboard-modulo <?= escaparHtml($accion['clase']) ?>" href="<?= escaparHtml(rutaBasePublica() . $accion['ruta']) ?>">
            <span class="dashboard-modulo-icono" aria-hidden="true"><?= $iconosDashboard[$accion['icono']] ?></span>
            <span class="dashboard-modulo-etiqueta"><?= escaparHtml($accion['texto']) ?></span>
            <strong><?= escaparHtml($accion['titulo']) ?></strong>
            <span class="dashboard-modulo-detalle"><?= escaparHtml($accion['detalle']) ?></span>
            <span class="dashboard-modulo-ir" aria-hidden="true">→</span>
        </a>
    <?php endforeach; ?>
</section>
<script>
const menuEmpleado = document.querySelector('#menuSemanaEmpleado');
const semanaInicioEmpleado = <?= json_encode($inicioSemana, JSON_UNESCAPED_SLASHES) ?>;
const rutaReservasEmpleado = <?= json_encode(rutaBasePublica() . '/empleado/reservas.php', JSON_UNESCAPED_SLASHES) ?>;

function elementoMenu(etiqueta, texto, clase = '') {
    const elemento = document.createElement(etiqueta);
    if (clase) elemento.className = clase;
    elemento.textContent = texto ?? '';
    return elemento;
}

function dibujarMenuSemana(menus) {
    menuEmpleado.replaceChildren();
    const semana = menus.filter((menu) => menu.fecha >= semanaInicioEmpleado && menu.fecha <= sumarDiasSemana(semanaInicioEmpleado, 3));
    if (!semana.length) {
        menuEmpleado.append(elementoMenu('p', 'El menú de esta semana aún no está publicado.', 'menu-semanal-vacio'));
        menuEmpleado.setAttribute('aria-busy', 'false');
        return;
    }

    for (const menu of semana) {
        const tarjeta = document.createElement('article');
        tarjeta.className = 'menu-semana-card';
        const foto = document.createElement('div');
        foto.className = 'menu-semana-foto';
        if (menu.imagen_path) {
            const imagen = document.createElement('img');
            imagen.src = menu.imagen_path;
            imagen.alt = `Foto del menú del ${menu.fecha}`;
            imagen.loading = 'lazy';
            imagen.addEventListener('error', () => foto.classList.add('sin-foto'));
            foto.append(imagen);
        } else {
            foto.classList.add('sin-foto');
            const ilustracion = document.createElement('span');
            ilustracion.className = 'menu-semana-ilustracion';
            ilustracion.setAttribute('aria-hidden', 'true');
            ilustracion.textContent = '✳';
            foto.append(ilustracion, elementoMenu('span', 'Menú del día', 'menu-semana-foto-texto'));
        }

        const contenido = document.createElement('div');
        contenido.className = 'menu-semana-contenido';
        const fecha = new Date(`${menu.fecha}T12:00:00`);
        contenido.append(elementoMenu('span', fecha.toLocaleDateString('es-BO', { weekday: 'long', day: 'numeric', month: 'long' }), 'menu-semana-fecha'));
        for (const [titulo, valor] of [['Sopa', menu.sopa], ['Plato principal', menu.plato_principal], ['Postre', menu.postre]]) {
            const fila = document.createElement('p');
            const nombrePlato = elementoMenu('strong', `${titulo} · `);
            fila.append(nombrePlato, document.createTextNode(valor ?? ''));
            contenido.append(fila);
        }
        const reservar = document.createElement('a');
        reservar.className = 'menu-semana-reservar';
        reservar.href = `${rutaReservasEmpleado}?fecha=${encodeURIComponent(menu.fecha)}`;
        reservar.textContent = 'Reservar este día';
        contenido.append(reservar);
        tarjeta.append(foto, contenido);
        menuEmpleado.append(tarjeta);
    }
    menuEmpleado.setAttribute('aria-busy', 'false');
}

function sumarDiasSemana(fecha, dias) {
    const dia = new Date(`${fecha}T12:00:00`);
    dia.setDate(dia.getDate() + dias);
    return dia.toISOString().slice(0, 10);
}

fetch(`../api/menus.php?fecha=${encodeURIComponent(semanaInicioEmpleado)}`, { headers: { Accept: 'application/json' } })
    .then(async (respuesta) => {
        const resultado = await respuesta.json();
        if (!respuesta.ok || !resultado.exito) throw new Error('No se pudo cargar el menú.');
        dibujarMenuSemana(resultado.menus ?? []);
    })
    .catch(() => {
        menuEmpleado.replaceChildren(elementoMenu('p', 'No se pudo cargar el menú de esta semana.', 'menu-semanal-vacio'));
        menuEmpleado.setAttribute('aria-busy', 'false');
    });
</script>
<?php require __DIR__ . '/../_dashboard-end.php'; ?>
