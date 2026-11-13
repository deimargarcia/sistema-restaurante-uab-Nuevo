<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Empleados\RegistrarEmpleado;

final readonly class ControladorEmpleados
{
    public function __construct(private RegistrarEmpleado $registrarEmpleado)
    {
    }

    public function registrar(array $datos): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
            return ['exito' => false, 'mensaje' => 'Solo administración puede registrar empleados.'];
        }

        $resultado = $this->registrarEmpleado->ejecutar($datos);

        return [
            'exito' => $resultado->exitoso,
            'mensaje' => $resultado->mensaje,
            'empleado_id' => $resultado->empleadoId,
        ];
    }
}
