<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Configuracion\ActualizarConfiguracion;
use Restaurante\Application\Configuracion\ConsultarConfiguracion;
use Restaurante\Application\Configuracion\GuardarDiaCalendario;

final readonly class ControladorConfiguracion
{
    public function __construct(
        private ActualizarConfiguracion $actualizar,
        private ConsultarConfiguracion $consultar,
        private GuardarDiaCalendario $guardarDia,
    ) {}

    public function parametros(): array
    {
        if (!$this->esAdmin()) return ['exito'=>false,'mensaje'=>'Solo administración puede consultar parámetros.'];
        return ['exito'=>true,'configuracion'=>$this->consultar->ejecutar()];
    }

    public function actualizar(array $datos): array
    {
        if (!$this->esAdmin()) return ['exito'=>false,'mensaje'=>'Solo administración puede cambiar parámetros.'];
        $resultado=$this->actualizar->ejecutar($datos);
        return ['exito'=>$resultado->exitoso,'mensaje'=>$resultado->mensaje];
    }

    public function guardarDia(array $datos): array
    {
        if (!$this->esAdmin()) return ['exito'=>false,'mensaje'=>'Solo administración puede gestionar el calendario.'];
        $resultado=$this->guardarDia->ejecutar($datos);
        return ['exito'=>$resultado->exitoso,'mensaje'=>$resultado->mensaje];
    }

    public function calendario(int $anio,int $mes): array
    {
        if (!$this->esAdmin()) return ['exito'=>false,'mensaje'=>'Solo administración puede consultar el calendario.'];
        return ['exito'=>true,'dias'=>$this->consultar->calendario($anio,$mes)];
    }

    private function esAdmin(): bool { return ($_SESSION['tipo_cuenta']??'')==='ADMIN'; }
}
