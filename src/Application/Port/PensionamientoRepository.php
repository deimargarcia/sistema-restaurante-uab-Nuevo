<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

use Restaurante\Domain\Entity\Pensionamiento;

interface PensionamientoRepository
{
    public function tipoCuentaTitular(int $titularId): ?string;

    public function familiarActivoPertenece(int $titularId, int $familiarId): bool;

    /** @return list<array{id:int,nombre:string,parentesco:string}> */
    public function familiaresActivosDelTitular(int $titularId): array;

    /** @return list<array{id:int,nombre_completo:string,codigo_empleado:?string}> */
    public function titularesPensionados(): array;

    public function feriadosDelMes(int $anio, int $mes): array;

    /** Guarda la afiliación y sus reservas automáticas como una sola operación. */
    public function registrarConReservas(Pensionamiento $pensionamiento, int $registradoPorId, array $fechas): int;
}
