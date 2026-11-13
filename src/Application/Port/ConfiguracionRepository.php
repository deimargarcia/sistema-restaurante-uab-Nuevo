<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

use Restaurante\Domain\Entity\ConfiguracionSistema;
use Restaurante\Domain\Entity\DiaCalendario;

interface ConfiguracionRepository
{
    public function obtener(): array;
    public function guardar(ConfiguracionSistema $configuracion): void;
    public function guardarDia(DiaCalendario $dia): void;
    /** @return list<array<string,mixed>> */
    public function listarMes(int $anio, int $mes): array;
}
