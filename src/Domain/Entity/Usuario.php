<?php
declare(strict_types=1);

namespace Restaurante\Domain\Entity;

final readonly class Usuario
{
    public function __construct(
        public int $id,
        public string $nombreCompleto,
        public string $correo,
        public ?string $ci,
        public string $contrasenaHash,
        public string $tipoCuenta,
        public ?string $codigoEmpleado,
        public bool $activo,
    ) {
        if ($id < 1) {
            throw new \InvalidArgumentException('El ID del usuario debe ser positivo.');
        }
    }

    public function verificarContrasena(string $contrasena): bool
    {
        return password_verify($contrasena, $this->contrasenaHash);
    }
}
