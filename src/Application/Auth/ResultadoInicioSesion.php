<?php
declare(strict_types=1);

namespace Restaurante\Application\Auth;

final readonly class ResultadoInicioSesion
{
    private function __construct(
        public bool $exitoso,
        public string $mensaje,
        public ?int $usuarioId = null,
        public ?string $nombreCompleto = null,
        public ?string $tipoCuenta = null,
        public ?string $codigoEmpleado = null,
    ) {
    }

    public static function exito(int $id, string $nombre, string $tipo, ?string $codigo): self
    {
        return new self(true, 'Inicio de sesión correcto.', $id, $nombre, $tipo, $codigo);
    }

    public static function rechazo(string $mensaje): self
    {
        return new self(false, $mensaje);
    }
}
