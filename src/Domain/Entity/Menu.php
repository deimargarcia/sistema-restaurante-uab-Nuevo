<?php
declare(strict_types=1);

namespace Restaurante\Domain\Entity;

final readonly class Menu
{
    public function __construct(
        public string $fecha,
        public string $sopa,
        public string $platoPrincipal,
        public string $postre,
        public int $publicadoPorId,
        public ?string $imagen = null,
    ) {
        $fechaObjeto = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaObjeto || $fechaObjeto->format('Y-m-d') !== $fecha) {
            throw new \InvalidArgumentException('La fecha del menú no es válida.');
        }
        foreach ([$sopa, $platoPrincipal, $postre] as $plato) {
            if (trim($plato) === '' || mb_strlen(trim($plato)) > 150) {
                throw new \InvalidArgumentException('Cada plato es obligatorio y admite hasta 150 caracteres.');
            }
        }
        if ($publicadoPorId < 1) {
            throw new \InvalidArgumentException('El administrador no es válido.');
        }
        if ($imagen !== null && filter_var($imagen, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('La dirección de la imagen no es válida.');
        }
    }
}
