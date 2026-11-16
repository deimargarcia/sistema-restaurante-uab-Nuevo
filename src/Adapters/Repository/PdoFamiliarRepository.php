<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use PDOException;
use Restaurante\Application\Familiares\FamiliarDuplicado;
use Restaurante\Application\Port\AdministracionFamiliarRepository;
use Restaurante\Application\Port\FamiliarRepository;
use Restaurante\Domain\Entity\Familiar;

final readonly class PdoFamiliarRepository implements FamiliarRepository, AdministracionFamiliarRepository
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

    public function ciExiste(string $ci): bool
    {
        $consulta = $this->conexion->prepare(
            'SELECT 1 FROM familiares WHERE ci = :ci_familiar
             UNION ALL
             SELECT 1 FROM usuarios WHERE ci = :ci_usuario
             LIMIT 1'
        );
        $consulta->execute(['ci_familiar' => $ci, 'ci_usuario' => $ci]);
        return $consulta->fetchColumn() !== false;
    }

    public function crear(Familiar $familiar): int
    {
        $consulta = $this->conexion->prepare(
            'INSERT INTO familiares (empleado_titular_id, nombre, ci, parentesco, estado)
             VALUES (:titular_id, :nombre, :ci, :parentesco, \'ACTIVO\')'
        );
        try {
            $consulta->execute([
                'titular_id' => $familiar->titularId,
                'nombre' => $familiar->nombre,
                'ci' => $familiar->ci,
                'parentesco' => $familiar->parentesco,
            ]);
        } catch (PDOException $error) {
            if ((string) $error->getCode() === '23000') {
                throw new FamiliarDuplicado('El CI ya está registrado como familiar.', previous: $error);
            }
            throw $error;
        }

        return (int) $this->conexion->lastInsertId();
    }

    public function listarFamiliares(): array
    {
        $consulta = $this->conexion->query(
            "SELECT f.id, f.empleado_titular_id, f.nombre, f.ci, f.parentesco, f.estado,
                    CONCAT_WS(' ', NULLIF(u.nombre, ''), NULLIF(u.apellido, '')) AS titular_nombre,
                    u.codigo_empleado
             FROM familiares f
             INNER JOIN usuarios u ON u.id = f.empleado_titular_id
             ORDER BY f.estado = 'ACTIVO' DESC, u.apellido, u.nombre, f.nombre"
        );
        $familiares = $consulta->fetchAll(PDO::FETCH_ASSOC);
        foreach ($familiares as &$familiar) {
            $familiar['id'] = (int) $familiar['id'];
            $familiar['empleado_titular_id'] = (int) $familiar['empleado_titular_id'];
        }
        unset($familiar);

        return $familiares;
    }

    public function ciUsadoPorOtro(string $ci, int $familiarId): bool
    {
        $consulta = $this->conexion->prepare(
            'SELECT 1 FROM familiares WHERE ci = :ci_familiar AND id <> :id
             UNION ALL
             SELECT 1 FROM usuarios WHERE ci = :ci_usuario
             LIMIT 1'
        );
        $consulta->execute(['ci_familiar' => $ci, 'id' => $familiarId, 'ci_usuario' => $ci]);
        return $consulta->fetchColumn() !== false;
    }

    public function actualizar(int $familiarId, array $datos): bool
    {
        $consulta = $this->conexion->prepare(
            'UPDATE familiares SET nombre = :nombre, ci = :ci, parentesco = :parentesco WHERE id = :id'
        );
        try {
            $consulta->execute([
                'nombre' => $datos['nombre'],
                'ci' => $datos['ci'],
                'parentesco' => $datos['parentesco'],
                'id' => $familiarId,
            ]);
        } catch (PDOException $error) {
            if ((string) $error->getCode() === '23000') {
                throw new FamiliarDuplicado('El CI ya está registrado en otro familiar.', previous: $error);
            }
            throw $error;
        }

        return $consulta->rowCount() > 0 || $this->familiarExiste($familiarId);
    }

    public function cambiarEstado(int $familiarId, string $estado): bool
    {
        $consulta = $this->conexion->prepare(
            'UPDATE familiares SET estado = :estado_nuevo WHERE id = :id AND estado <> :estado_actual'
        );
        $consulta->execute(['estado_nuevo' => $estado, 'estado_actual' => $estado, 'id' => $familiarId]);
        return $consulta->rowCount() > 0;
    }

    private function familiarExiste(int $familiarId): bool
    {
        $consulta = $this->conexion->prepare('SELECT 1 FROM familiares WHERE id = :id LIMIT 1');
        $consulta->execute(['id' => $familiarId]);
        return $consulta->fetchColumn() !== false;
    }
}
