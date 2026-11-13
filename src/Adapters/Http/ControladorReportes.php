<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Reportes\ConsultarReporteMensual;
use Restaurante\Application\Reportes\GenerarReporteMensual;

final readonly class ControladorReportes
{
    public function __construct(private GenerarReporteMensual $generar, private ConsultarReporteMensual $consultar)
    {
    }

    public function generar(int $anio, int $mes): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
            return ['exito' => false, 'mensaje' => 'Solo administración puede generar reportes.'];
        }
        return $this->generar->ejecutar($anio, $mes);
    }

    public function consultar(int $anio, int $mes): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
            return ['exito' => false, 'mensaje' => 'Solo administración puede consultar reportes.'];
        }
        return ['exito' => true, 'filas' => $this->consultar->ejecutar($anio, $mes)];
    }
}
