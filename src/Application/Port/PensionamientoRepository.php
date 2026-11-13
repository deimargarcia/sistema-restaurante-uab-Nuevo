<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

use Restaurante\Domain\Entity\Pensionamiento;

interface PensionamientoRepository
{
    public function tipoCuentaTitular(int $titularId): ?string;

    public function familiarActivoPertenece(int $titularId, int $familiarId): bool;

    public function feriadosDelMes(int $anio, int $mes): array;

    /** Guarda la afiliación y sus reservas automáticas como una sola operación. */
    public function registrarConReservas(Pensionamiento $pensionamiento, int $registradoPorId, array $fechas): int;
}
