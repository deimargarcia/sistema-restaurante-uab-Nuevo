<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

use Restaurante\Domain\Entity\Usuario;

/** Puerto requerido por el caso de uso; la implementación vive en Adaptadores. */
interface UsuarioRepository
{
    public function buscarPorIdentificador(string $correoOCi): ?Usuario;
}
