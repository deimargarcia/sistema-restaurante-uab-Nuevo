<?php
declare(strict_types=1);

namespace Restaurante\Domain\Entity;

final readonly class DiaCalendario
{
    public function __construct(public string $fecha, public bool $esFeriado, public string $descripcion = '')
    {
        $fechaObjeto = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaObjeto || $fechaObjeto->format('Y-m-d') !== $fecha) {
            throw new \InvalidArgumentException('La fecha del calendario no es válida.');
        }
        if (mb_strlen(trim($descripcion)) > 200) {
            throw new \InvalidArgumentException('La descripción admite hasta 200 caracteres.');
        }
        if (!$esFeriado && trim($descripcion) !== '') {
            throw new \InvalidArgumentException('Un día operativo no necesita descripción de feriado.');
        }
    }
}
