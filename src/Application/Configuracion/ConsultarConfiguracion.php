<?php
declare(strict_types=1);

namespace Restaurante\Application\Configuracion;

use Restaurante\Application\Port\ConfiguracionRepository;

final readonly class ConsultarConfiguracion
{
    public function __construct(private ConfiguracionRepository $configuracion) {}
    public function ejecutar(): array { return $this->configuracion->obtener(); }
    public function calendario(int $anio, int $mes): array { return checkdate($mes, 1, $anio) ? $this->configuracion->listarMes($anio, $mes) : []; }
}
