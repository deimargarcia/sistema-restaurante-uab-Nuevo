<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use Restaurante\Application\Port\FamiliarRepository;
use Restaurante\Domain\Entity\Familiar;

final readonly class PdoFamiliarRepository implements FamiliarRepository
{
    public function __construct(private PDO $conexion)
    {
    }

    public function listarTitulares(): array
    {
        $consulta = $this->conexion->query(
            "SELECT id, CONCAT_WS(' ', NULLIF(nombre, ''), NULLIF(apellido, '')) AS nombre_completo,
                    codigo_empleado
             FROM usuarios
             WHERE tipo_cuenta IN ('PENSIONADO', 'NO_PENSIONADO') AND activo = 1
             ORDER BY apellido, nombre"
        );

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function titularEmpleadoExiste(int $titularId): bool
    {
        $consulta = $this->conexion->prepare(
            "SELECT 1 FROM usuarios
             WHERE id = :id AND tipo_cuenta IN ('PENSIONADO', 'NO_PENSIONADO') AND activo = 1
             LIMIT 1"
        );
        $consulta->execute(['id' => $titularId]);
        return $consulta->fetchColumn() !== false;
    }

    public function crear(Familiar $familiar): int
    {
        $consulta = $this->conexion->prepare(
            'INSERT INTO familiares (empleado_titular_id, nombre, parentesco, estado)
             VALUES (:titular_id, :nombre, :parentesco, \'ACTIVO\')'
        );
        $consulta->execute([
            'titular_id' => $familiar->titularId,
            'nombre' => $familiar->nombre,
            'parentesco' => $familiar->parentesco,
        ]);

        return (int) $this->conexion->lastInsertId();
    }
}
