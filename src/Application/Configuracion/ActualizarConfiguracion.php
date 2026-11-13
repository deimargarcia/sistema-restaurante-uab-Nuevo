<?php
declare(strict_types=1);

namespace Restaurante\Application\Configuracion;

use Restaurante\Application\Port\ConfiguracionRepository;
use Restaurante\Domain\Entity\ConfiguracionSistema;

final readonly class ActualizarConfiguracion
{
    public function __construct(private ConfiguracionRepository $configuracion) {}

    public function ejecutar(array $datos): ResultadoConfiguracion
    {
        $tarifaPensionado = $this->tarifa($datos['tarifa_pensionado'] ?? null);
        $tarifaEsporadico = $this->tarifa($datos['tarifa_esporadico'] ?? null);
        $minimo = filter_var($datos['minimo_almuerzos_mes'] ?? null, FILTER_VALIDATE_INT);
        $hora = $datos['hora_limite'] ?? null;
        if ($tarifaPensionado === null || $tarifaEsporadico === null) {
            return ResultadoConfiguracion::rechazo('Las tarifas deben ser números entre Bs 0 y Bs 999999,99.');
        }
        if ($minimo === false || $minimo < 0 || $minimo > 31) {
            return ResultadoConfiguracion::rechazo('El mínimo mensual debe estar entre 0 y 31 almuerzos.');
        }
        if (!is_string($hora) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $hora)) {
            return ResultadoConfiguracion::rechazo('La hora límite debe tener un formato válido de 24 horas.');
        }
        $this->configuracion->guardar(new ConfiguracionSistema($tarifaPensionado, $tarifaEsporadico, $minimo, $hora));
        return ResultadoConfiguracion::exito('Parámetros actualizados.');
    }

    private function tarifa(mixed $valor): ?float
    {
        if (!is_numeric($valor) || !is_finite((float) $valor)) return null;
        $tarifa = round((float) $valor, 2);
        return $tarifa >= 0 && $tarifa <= 999999.99 ? $tarifa : null;
    }
}
