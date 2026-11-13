<?php
declare(strict_types=1);

namespace Restaurante\Application\Reportes;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Restaurante\Application\Port\ReporteRepository;
use Restaurante\Domain\Entity\ReporteMensual;

final readonly class GenerarReporteMensual
{
    private Closure $ahora;

    public function __construct(private ReporteRepository $reportes, ?Closure $ahora = null)
    {
        $this->ahora = $ahora ?? static fn (): DateTimeImmutable => new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
    }

    public function ejecutar(int $anio, int $mes): array
    {
        if (!checkdate($mes, 1, $anio)) {
            return ['exito' => false, 'mensaje' => 'Selecciona un periodo válido.'];
        }
        $inicioSiguiente = (new DateTimeImmutable(sprintf('%04d-%02d-01', $anio, $mes)))->modify('first day of next month');
        $inicioActual = ($this->ahora)()->modify('first day of this month');
        if ($inicioSiguiente > $inicioActual) {
            return ['exito' => false, 'mensaje' => 'El reporte se genera cuando termina el mes seleccionado.'];
        }

        $ahora = ($this->ahora)();
        $reglas = $this->reportes->reglas();
        $this->reportes->actualizarEstados($ahora->format('Y-m-d'), $ahora->format('H:i:s'), $reglas['hora_limite']);
        $filas = [];
        foreach ($this->reportes->datosDelMes($anio, $mes) as $titular) {
            $detalleFamiliares = [];
            $totalAlmuerzosFamiliares = 0;
            $totalPenalizacionesFamiliares = 0.0;
            $montoFamiliares = 0.0;

            foreach ($titular['familiares'] as $familiar) {
                $mensuales = (int) $familiar['almuerzos_pensionados'];
                $esporadicos = (int) $familiar['almuerzos_esporadicos'];
                $almuerzos = $mensuales + $esporadicos;
                $monto = $mensuales * $reglas['tarifa_pensionado'] + $esporadicos * $reglas['tarifa_esporadico'];
                $penalizacion = !empty($familiar['afiliado'])
                    ? max(0, $reglas['minimo_almuerzos_mes'] - $mensuales) * $reglas['tarifa_pensionado']
                    : 0.0;
                $detalleFamiliares[] = [
                    'nombre' => (string) $familiar['nombre'],
                    'almuerzos' => $almuerzos,
                    'monto' => round($monto, 2),
                    'penalizacion' => round($penalizacion, 2),
                    'total_familiar' => round($monto + $penalizacion, 2),
                ];
                $totalAlmuerzosFamiliares += $almuerzos;
                $montoFamiliares += $monto;
                $totalPenalizacionesFamiliares += $penalizacion;
            }

            $mensualesTitular = (int) $titular['almuerzos_pensionados'];
            $esporadicosTitular = (int) $titular['almuerzos_esporadicos'];
            $montoMensualTitular = !empty($titular['afiliado'])
                ? max($reglas['minimo_almuerzos_mes'], $mensualesTitular) * $reglas['tarifa_pensionado']
                : $mensualesTitular * $reglas['tarifa_pensionado'];
            $montoTitular = round($montoMensualTitular + $esporadicosTitular * $reglas['tarifa_esporadico'], 2);
            $reporte = new ReporteMensual(
                $anio,
                $mes,
                (string) ($titular['codigo_empleado'] ?: 'SIN_CODIGO-' . (int) $titular['id']),
                (string) $titular['nombre'],
                $mensualesTitular + $esporadicosTitular,
                $montoTitular,
                $detalleFamiliares,
                $totalAlmuerzosFamiliares,
                round($totalPenalizacionesFamiliares, 2),
                round($montoTitular + $montoFamiliares + $totalPenalizacionesFamiliares, 2),
            );
            $fila = $reporte->aArray();
            $this->reportes->guardar($fila);
            $filas[] = $fila;
        }

        return ['exito' => true, 'mensaje' => 'Reporte mensual generado.', 'total_empleados' => count($filas), 'filas' => $filas];
    }
}
