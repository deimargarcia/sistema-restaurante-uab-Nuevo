<?php
declare(strict_types=1);

namespace Restaurante\Application\Familiares;

final readonly class ResultadoRegistroFamiliar
{
    private function __construct(
        public bool $exitoso,
        public string $mensaje,
        public ?int $familiarId = null,
    ) {
    }

    public static function exito(int $familiarId): self
    {
        return new self(true, 'Familiar vinculado correctamente al empleado.', $familiarId);
    }

    public static function rechazo(string $mensaje): self
    {
        return new self(false, $mensaje);
    }
}
