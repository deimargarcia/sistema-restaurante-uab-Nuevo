<?php
declare(strict_types=1);

namespace Restaurante\Domain\Entity;

final readonly class Pensionamiento
{
    public function __construct(
        public int $titularId,
        public int $anio,
        public int $mes,
        public ?int $familiarId = null,
    ) {
        if ($titularId < 1 || ($familiarId !== null && $familiarId < 1)) {
            throw new \InvalidArgumentException('El titular y el familiar deben tener identificadores válidos.');
        }
        if (!checkdate($mes, 1, $anio)) {
            throw new \InvalidArgumentException('El periodo de pensionamiento no es válido.');
        }
    }
}
