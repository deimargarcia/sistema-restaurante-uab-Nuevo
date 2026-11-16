<?php
declare(strict_types=1);

namespace Restaurante\Application\Empleados;

use Restaurante\Application\Port\EmpleadoAdministracionRepository;

final readonly class CambiarEstadoEmpleado
{
    public function __construct(private EmpleadoAdministracionRepository $empleados)
    {
    }

    public function ejecutar(int $id, bool $activo): ResultadoGestionEmpleado
    {
        if ($id < 1) {
            return ResultadoGestionEmpleado::rechazo('Selecciona un empleado válido.');
        }
        if (!$this->empleados->cambiarEstado($id, $activo)) {
            return ResultadoGestionEmpleado::rechazo('No se encontró el empleado o su estado ya era el solicitado.');
        }

        return ResultadoGestionEmpleado::exito($activo ? 'La cuenta quedó activa.' : 'La cuenta quedó desactivada.');
    }
}
