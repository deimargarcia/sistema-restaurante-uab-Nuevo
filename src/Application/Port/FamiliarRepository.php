<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

use Restaurante\Domain\Entity\Familiar;

interface FamiliarRepository
{
    public function listarTitulares(): array;

    public function titularEmpleadoExiste(int $titularId): bool;

    public function crear(Familiar $familiar): int;
}
