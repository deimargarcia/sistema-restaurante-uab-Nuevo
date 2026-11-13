<?php
declare(strict_types=1);

namespace Restaurante\Application\Empleados;

final readonly class ResultadoRegistroEmpleado
{
    private function __construct(
        public bool $exitoso,
        public string $mensaje,
        public ?int $empleadoId = null,
    ) {
    }

    public static function exito(int $empleadoId): self
    {
        return new self(true, 'Empleado registrado correctamente.', $empleadoId);
    }

    public static function rechazo(string $mensaje): self
    {
        return new self(false, $mensaje);
    }
}
