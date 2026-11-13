<?php
declare(strict_types=1);

namespace Restaurante\Application\Configuracion;

use Restaurante\Application\Port\ConfiguracionRepository;
use Restaurante\Domain\Entity\DiaCalendario;

final readonly class GuardarDiaCalendario
{
    public function __construct(private ConfiguracionRepository $calendario) {}

    public function ejecutar(array $datos): ResultadoConfiguracion
    {
        $valor = filter_var($datos['es_feriado'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($valor === null) return ResultadoConfiguracion::rechazo('Indica si la fecha será feriado o día operativo.');
        try {
            $dia = new DiaCalendario(
                trim((string) ($datos['fecha'] ?? '')),
                $valor,
                trim((string) ($datos['descripcion'] ?? '')),
            );
        } catch (\InvalidArgumentException $error) {
            return ResultadoConfiguracion::rechazo($error->getMessage());
        }
        $this->calendario->guardarDia($dia);
        return ResultadoConfiguracion::exito($valor ? 'Feriado registrado.' : 'Fecha marcada como día operativo.');
    }
}
