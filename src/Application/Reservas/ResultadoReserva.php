<?php
declare(strict_types=1);

namespace Restaurante\Application\Reservas;

final readonly class ResultadoReserva
{
    private function __construct(
        public bool $exitoso,
        public string $mensaje,
        public ?int $reservaId = null,
        public float $tarifa = 0.0,
    ) {
    }

    public static function exito(int $id, float $tarifa, string $mensaje): self
    {
        return new self(true, $mensaje, $id, $tarifa);
    }

    public static function rechazo(string $mensaje): self
    {
        return new self(false, $mensaje);
    }
}
