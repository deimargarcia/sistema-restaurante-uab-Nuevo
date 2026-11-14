<?php
declare(strict_types=1);

namespace Restaurante\Application\Configuracion;

use Restaurante\Application\Port\ConfiguracionRepository;
use Restaurante\Domain\Entity\ConfiguracionSistema;

final readonly class ActualizarConfiguracion
{
    private const TARIFA_PENSIONADO = 15.0;
    private const TARIFA_ESPORADICO = 18.0;

    public function __construct(private ConfiguracionRepository $configuracion) {}

    public function ejecutar(array $datos): ResultadoConfiguracion
    {
        $minimo = filter_var($datos['minimo_almuerzos_mes'] ?? null, FILTER_VALIDATE_INT);
        $hora = $datos['hora_limite'] ?? null;
        if ($minimo === false || $minimo < 0 || $minimo > 31) {
            return ResultadoConfiguracion::rechazo('El mínimo mensual debe estar entre 0 y 31 almuerzos.');
        }
        if (!is_string($hora) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $hora)) {
            return ResultadoConfiguracion::rechazo('La hora límite debe tener un formato válido de 24 horas.');
        }
        $this->configuracion->guardar(new ConfiguracionSistema(self::TARIFA_PENSIONADO, self::TARIFA_ESPORADICO, $minimo, $hora));
        return ResultadoConfiguracion::exito('Parámetros actualizados.');
    }
}
