<?php
declare(strict_types=1);

namespace Restaurante\Application\Familiares;

use Restaurante\Application\Port\AdministracionFamiliarRepository;

final readonly class ListarFamiliares
{
    public function __construct(private AdministracionFamiliarRepository $familiares) {}

    public function ejecutar(): array
    {
        return $this->familiares->listarFamiliares();
    }
}
