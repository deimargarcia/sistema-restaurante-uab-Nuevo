<?php
declare(strict_types=1);

namespace Restaurante\Application\Reportes;

use Restaurante\Application\Port\ReporteRepository;

final readonly class ConsultarReporteMensual
{
    public function __construct(private ReporteRepository $reportes) {}

    public function ejecutar(int $anio, int $mes): array
    {
        return checkdate($mes, 1, $anio) ? $this->reportes->listar($anio, $mes) : [];
    }
}
