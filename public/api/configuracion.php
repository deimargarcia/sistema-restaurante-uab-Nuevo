<?php
declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';
use Restaurante\Adapters\Http\ControladorConfiguracion;
use Restaurante\Adapters\Repository\PdoConfiguracionRepository;
use Restaurante\Application\Configuracion\ActualizarConfiguracion;
use Restaurante\Application\Configuracion\ConsultarConfiguracion;
use Restaurante\Application\Configuracion\GuardarDiaCalendario;
use Restaurante\Infrastructure\Database\Conexion;

iniciarSesionWeb(); header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['usuario_id']) || ($_SESSION['tipo_cuenta']??'')!=='ADMIN') { http_response_code(403); echo json_encode(['exito'=>false,'mensaje'=>'Solo administración puede gestionar la configuración.'],JSON_UNESCAPED_UNICODE); exit; }
$metodo=$_SERVER['REQUEST_METHOD']??'GET'; if(!in_array($metodo,['GET','POST'],true)){http_response_code(405);echo json_encode(['exito'=>false,'mensaje'=>'Método no permitido.']);exit;}
try {
    $repo=new PdoConfiguracionRepository(Conexion::crearDesdeEntorno());
    $controlador=new ControladorConfiguracion(new ActualizarConfiguracion($repo),new ConsultarConfiguracion($repo),new GuardarDiaCalendario($repo));
    if($metodo==='GET' && ($_GET['accion']??'')==='calendario'){
        $periodo=(string)($_GET['periodo']??'');
        if(!preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/',$periodo,$p)){$respuesta=['exito'=>false,'mensaje'=>'Selecciona un mes válido.'];http_response_code(422);}
        else{$respuesta=$controlador->calendario((int)$p[1],(int)$p[2]);http_response_code(200);}
    } elseif($metodo==='GET'){$respuesta=$controlador->parametros();http_response_code(200);}
    elseif(($_POST['accion']??'')==='actualizar'){$respuesta=$controlador->actualizar($_POST);http_response_code($respuesta['exito']?200:422);}
    elseif(($_POST['accion']??'')==='guardar_dia'){$respuesta=$controlador->guardarDia($_POST);http_response_code($respuesta['exito']?200:422);}
    else{$respuesta=['exito'=>false,'mensaje'=>'Acción no válida.'];http_response_code(422);}
    echo json_encode($respuesta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}catch(Throwable $error){error_log($error->__toString());http_response_code(500);echo json_encode(['exito'=>false,'mensaje'=>'No se pudo actualizar la configuración. Inténtalo de nuevo.'],JSON_UNESCAPED_UNICODE);}
