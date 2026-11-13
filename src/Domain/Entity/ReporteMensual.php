<?php
declare(strict_types=1);

namespace Restaurante\Domain\Entity;

final readonly class ReporteMensual
{
    public function __construct(
        public int $anio,
        public int $mes,
        public string $codigoEmpleado,
        public string $nombreEmpleado,
        public int $almuerzosTitular,
        public float $montoTitular,
        public array $detalleFamiliares,
        public int $almuerzosFamiliares,
        public float $penalizacionFamiliares,
        public float $totalDescuento,
    ) {
        if (!checkdate($mes, 1, $anio) || $nombreEmpleado === '') {
            throw new \InvalidArgumentException('El periodo o el nombre del empleado no son válidos.');
        }
        if (min($almuerzosTitular, $almuerzosFamiliares) < 0 || min($montoTitular, $penalizacionFamiliares, $totalDescuento) < 0) {
            throw new \InvalidArgumentException('Los totales del reporte no pueden ser negativos.');
        }
    }

    public function aArray(): array
    {
        return [
            'anio' => $this->anio,
            'mes' => $this->mes,
            'codigo_empleado' => $this->codigoEmpleado,
            'nombre_empleado' => $this->nombreEmpleado,
            'almuerzos_titular' => $this->almuerzosTitular,
            'monto_titular' => $this->montoTitular,
            'detalle_familiares' => $this->detalleFamiliares,
            'almuerzos_familiares' => $this->almuerzosFamiliares,
            'penalizacion_familiares' => $this->penalizacionFamiliares,
            'total_descuento' => $this->totalDescuento,
        ];
    }
}
