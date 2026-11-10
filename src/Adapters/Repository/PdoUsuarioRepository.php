<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use Restaurante\Domain\Entity\Usuario;
use Restaurante\Application\Port\UsuarioRepository;

final readonly class PdoUsuarioRepository implements UsuarioRepository
{
    public function __construct(private PDO $conexion)
    {
    }

    public function buscarPorCorreo(string $correo): ?Usuario
    {
        $consulta = $this->conexion->prepare(
            "SELECT id, CONCAT_WS(' ', NULLIF(nombre, ''), NULLIF(apellido, '')) AS nombre_completo,
                    correo, contrasena, tipo_cuenta, codigo_empleado, activo
             FROM usuarios WHERE correo = :correo LIMIT 1"
        );
        $consulta->execute(['correo' => $correo]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($fila === false) {
            return null;
        }

        return new Usuario(
            (int) $fila['id'],
            (string) $fila['nombre_completo'],
            (string) $fila['correo'],
            (string) $fila['contrasena'],
            (string) $fila['tipo_cuenta'],
            $fila['codigo_empleado'] !== null ? (string) $fila['codigo_empleado'] : null,
            (bool) $fila['activo'],
        );
    }
}
