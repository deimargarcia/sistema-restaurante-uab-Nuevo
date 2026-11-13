<?php
declare(strict_types=1);

namespace Restaurante\Application\Pensionamientos;

final readonly class ResultadoAfiliacion
{
    private function __construct(
        public bool $exitoso,
        public string $mensaje,
        public int $reservasCreadas = 0,
    ) {
    }

    public static function exito(int $reservasCreadas): self
    {
        return new self(true, 'Afiliación registrada y almuerzos programados.', $reservasCreadas);
    }

    public static function rechazo(string $mensaje): self
    {
        return new self(false, $mensaje);
    }
}
