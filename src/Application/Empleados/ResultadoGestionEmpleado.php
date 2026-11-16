<?php
declare(strict_types=1);

namespace Restaurante\Application\Empleados;

final readonly class ResultadoGestionEmpleado
{
    private function __construct(public bool $exitoso, public string $mensaje)
    {
    }

    public static function exito(string $mensaje): self
    {
        return new self(true, $mensaje);
    }

    public static function rechazo(string $mensaje): self
    {
        return new self(false, $mensaje);
    }
}
