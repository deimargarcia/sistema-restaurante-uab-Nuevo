<?php
declare(strict_types=1);

namespace Restaurante\Domain\Entity;

final readonly class Empleado
{
    public function __construct(
        public string $nombre,
        public string $apellido,
        public string $correo,
        public string $contrasenaHash,
        public string $celular,
        public string $ci,
        public string $departamento,
        public string $codigoEmpleado,
        public string $tipoCuenta,
    ) {
    }
}
