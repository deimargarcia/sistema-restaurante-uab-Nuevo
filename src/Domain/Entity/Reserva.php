<?php
declare(strict_types=1);

namespace Restaurante\Domain\Entity;

final readonly class Reserva
{
    public function __construct(
        public int $titularId,
        public ?int $familiarId,
        public string $fecha,
        public string $modalidad,
    ) {
        if ($titularId < 1 || ($familiarId !== null && $familiarId < 1)) {
            throw new \InvalidArgumentException('El titular y el familiar deben tener identificadores válidos.');
        }
        $fechaValida = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            throw new \InvalidArgumentException('La fecha de la reserva no es válida.');
        }
        if (!in_array($modalidad, ['PENSIONADO', 'ESPORADICO'], true)) {
            throw new \InvalidArgumentException('La modalidad de la reserva no es válida.');
        }
    }
}
