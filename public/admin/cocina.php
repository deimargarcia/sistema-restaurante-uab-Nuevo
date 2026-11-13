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
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Cocina | Restaurante Rumbo al Cielo</title><link rel="stylesheet" href="<?= escaparHtml(rutaBasePublica()) ?>/assets/css/app.css"></head>
<body class="pagina-panel"><main class="panel">
    <header class="panel-cabecera"><a class="marca marca-enlace" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/dashboard.php"><img src="<?= escaparHtml(rutaBasePublica()) ?>/assets/img/uab-favicon.png" alt=""><span><strong>Rumbo al Cielo</strong><small>ADMINISTRACIÓN · COCINA</small></span></a><a class="boton-secundario" href="<?= escaparHtml(rutaBasePublica()) ?>/admin/dashboard.php">Volver al inicio</a></header>
    <section class="panel-contenido panel-contenido-ancho"><span class="etiqueta etiqueta-verde">CONSOLIDADO DE COCINA</span><h1>Entregas del día</h1><p>El consolidado aparece después del horario límite. Marca cada almuerzo cuando se entregue; las reservas restantes pasarán a inasistencia cobrada al cerrar la jornada.</p>
        <form class="formulario-empleado" id="filtro-cocina"><div class="campo-formulario"><label for="fecha">Fecha de servicio</label><input id="fecha" type="date" value="<?= escaparHtml($hoy) ?>" required></div><button class="boton-formulario" type="submit">Ver consolidado</button></form>
        <p class="mensaje" id="mensaje-cocina" role="status" aria-live="polite"></p><div id="resumen-cocina" class="panel-tarjeta"></div><div id="reservas-cocina" class="lista-reservas">Cargando…</div>
    </section>
</main>
<script>
const formulario=document.querySelector('#filtro-cocina'); const fecha=document.querySelector('#fecha'); const resumen=document.querySelector('#resumen-cocina'); const tablaZona=document.querySelector('#reservas-cocina'); const mensaje=document.querySelector('#mensaje-cocina');
const estadoNombre={RESERVADO:'Reservado',PENDIENTE_ENTREGA:'Pendiente de entrega',ENTREGADO:'Entregado',COBRADO_INASISTENCIA:'Inasistencia cobrada',CANCELADO:'Cancelado'};
async function cargar(){
 const resp=await fetch(`../api/reservas.php?fecha=${encodeURIComponent(fecha.value)}`); const data=await resp.json(); if(!data.exito)throw new Error(data.mensaje);
 const filas=data.reservas; const cobrables=filas.filter(r=>['PENDIENTE_ENTREGA','ENTREGADO','COBRADO_INASISTENCIA'].includes(r.estado));
 const conteo={total:cobrables.length,titulares:cobrables.filter(r=>r.tipo_beneficiario==='TITULAR').length,familiares:cobrables.filter(r=>r.tipo_beneficiario==='FAMILIAR').length,entregados:cobrables.filter(r=>r.estado==='ENTREGADO').length,pendientes:cobrables.filter(r=>r.estado==='PENDIENTE_ENTREGA').length,inasistencias:cobrables.filter(r=>r.estado==='COBRADO_INASISTENCIA').length};
 resumen.replaceChildren(); for(const [titulo,valor] of Object.entries({"Almuerzos programados":conteo.total,"Titulares":conteo.titulares,"Familiares":conteo.familiares,"Entregados":conteo.entregados,"Pendientes":conteo.pendientes,"Inasistencias":conteo.inasistencias})){const item=document.createElement('span');item.textContent=`${titulo}: ${valor}`;resumen.append(item);}
 if(!filas.length){tablaZona.textContent='No hay reservas para esta fecha.';return;}
 const table=document.createElement('table');table.className='tabla-reservas';const head=document.createElement('thead');const hr=document.createElement('tr');for(const title of ['Titular','Código','Beneficiario','Estado','Acción']){const th=document.createElement('th');th.textContent=title;hr.append(th);}head.append(hr);const body=document.createElement('tbody');
 for(const reserva of filas){const tr=document.createElement('tr');for(const value of [reserva.nombre_titular,reserva.codigo_empleado,reserva.tipo_beneficiario==='FAMILIAR'?reserva.nombre_familiar:'Titular',estadoNombre[reserva.estado]??reserva.estado]){const td=document.createElement('td');td.textContent=value??'';tr.append(td);}const td=document.createElement('td');if(reserva.estado==='PENDIENTE_ENTREGA'){const button=document.createElement('button');button.type='button';button.className='boton-formulario';button.textContent='Registrar entrega';button.addEventListener('click',()=>registrar(reserva.id));td.append(button);}else{td.textContent='—';}tr.append(td);body.append(tr);}
 table.append(head,body);tablaZona.replaceChildren(table);
}
async function registrar(id){const body=new FormData();body.set('accion','registrar_entrega');body.set('reserva_id',id);const resp=await fetch('../api/reservas.php',{method:'POST',body});const data=await resp.json();mensaje.textContent=data.mensaje;mensaje.className=`mensaje ${data.exito?'mensaje-exito':'mensaje-error'}`;if(data.exito)await cargar();}
formulario.addEventListener('submit',e=>{e.preventDefault();cargar().catch(err=>{mensaje.textContent=err.message;mensaje.className='mensaje mensaje-error';});});
cargar().catch(err=>{tablaZona.textContent=err.message;});
</script></body></html>
