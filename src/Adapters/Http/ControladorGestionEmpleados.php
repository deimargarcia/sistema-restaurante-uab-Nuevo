<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Empleados\ActualizarEmpleado;
use Restaurante\Application\Empleados\CambiarEstadoEmpleado;
use Restaurante\Application\Empleados\ListarEmpleados;

final readonly class ControladorGestionEmpleados
{
    public function __construct(
        private ListarEmpleados $listarEmpleados,
        private ActualizarEmpleado $actualizarEmpleado,
        private CambiarEstadoEmpleado $cambiarEstadoEmpleado,
    ) {
    }

    public function listar(): array
    {
        if (!$this->esAdministracion()) {
            return ['exito' => false, 'mensaje' => 'Solo administración puede consultar empleados.'];
        }

        return ['exito' => true, 'empleados' => $this->listarEmpleados->ejecutar()];
    }

    public function actualizar(int $id, array $datos): array
    {
        if (!$this->esAdministracion()) {
            return ['exito' => false, 'mensaje' => 'Solo administración puede modificar empleados.'];
        }

        $resultado = $this->actualizarEmpleado->ejecutar($id, $datos);
        return ['exito' => $resultado->exitoso, 'mensaje' => $resultado->mensaje];
    }

    public function cambiarEstado(int $id, bool $activo): array
    {
        if (!$this->esAdministracion()) {
            return ['exito' => false, 'mensaje' => 'Solo administración puede cambiar el estado de empleados.'];
        }

        $resultado = $this->cambiarEstadoEmpleado->ejecutar($id, $activo);
        return ['exito' => $resultado->exitoso, 'mensaje' => $resultado->mensaje];
    }

    private function esAdministracion(): bool
    {
        return ($_SESSION['tipo_cuenta'] ?? '') === 'ADMIN';
    }
}
