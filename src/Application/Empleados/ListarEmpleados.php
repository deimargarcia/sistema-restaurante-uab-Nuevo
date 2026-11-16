<?php
declare(strict_types=1);

namespace Restaurante\Application\Empleados;

use Restaurante\Application\Port\EmpleadoAdministracionRepository;

final readonly class ListarEmpleados
{
    public function __construct(private EmpleadoAdministracionRepository $empleados)
    {
    }

    public function ejecutar(): array
    {
        return $this->empleados->listarAdministrables();
    }
}
