<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

use Restaurante\Domain\Entity\Empleado;

interface EmpleadoRepository
{
    public function correoExiste(string $correo): bool;

    public function ciExiste(string $ci): bool;

    public function codigoExiste(string $codigoEmpleado): bool;

    public function crear(Empleado $empleado): int;
}
