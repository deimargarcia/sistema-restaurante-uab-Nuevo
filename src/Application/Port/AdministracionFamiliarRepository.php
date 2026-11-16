<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

interface AdministracionFamiliarRepository
{
    /** @return list<array<string, mixed>> */
    public function listarFamiliares(): array;

    public function ciUsadoPorOtro(string $ci, int $familiarId): bool;

    /** @param array{nombre:string, ci:string, parentesco:string} $datos */
    public function actualizar(int $familiarId, array $datos): bool;

    public function cambiarEstado(int $familiarId, string $estado): bool;
}
