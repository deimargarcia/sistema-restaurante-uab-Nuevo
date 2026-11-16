<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use PDOException;
use Restaurante\Application\Empleados\EmpleadoDuplicado;
use Restaurante\Application\Port\EmpleadoAdministracionRepository;
use Restaurante\Application\Port\EmpleadoRepository;
use Restaurante\Domain\Entity\Empleado;

final readonly class PdoEmpleadoRepository implements EmpleadoRepository, EmpleadoAdministracionRepository
{
    public function __construct(private PDO $conexion)
    {
    }

    public function correoExiste(string $correo): bool
    {
        return $this->existe('correo', $correo);
    }

    public function ciExiste(string $ci): bool
    {
        return $this->existe('ci', $ci);
    }

    public function codigoExiste(string $codigoEmpleado): bool
    {
        return $this->existe('codigo_empleado', $codigoEmpleado);
    }

    public function crear(Empleado $empleado): int
    {
        $consulta = $this->conexion->prepare(
            'INSERT INTO usuarios
                (nombre, apellido, ci, departamento, celular, correo, contrasena,
                 tipo_cuenta, codigo_empleado, activo)
             VALUES
                (:nombre, :apellido, :ci, :departamento, :celular, :correo, :contrasena,
                 :tipo_cuenta, :codigo_empleado, 1)'
        );

        try {
            $consulta->execute([
                'nombre' => $empleado->nombre,
                'apellido' => $empleado->apellido,
                'ci' => $empleado->ci,
                'departamento' => $empleado->departamento,
                'celular' => $empleado->celular,
                'correo' => $empleado->correo,
                'contrasena' => $empleado->contrasenaHash,
                'tipo_cuenta' => $empleado->tipoCuenta,
                'codigo_empleado' => $empleado->codigoEmpleado,
            ]);
        } catch (PDOException $error) {
            if ((string) $error->getCode() === '23000') {
                throw new EmpleadoDuplicado('El correo, CI o código de empleado ya está registrado.', previous: $error);
            }
            throw $error;
        }

        return (int) $this->conexion->lastInsertId();
    }

    public function listarAdministrables(): array
    {
        $consulta = $this->conexion->query(
            "SELECT id, nombre, apellido, ci, departamento, celular, correo, codigo_empleado, tipo_cuenta, activo
             FROM usuarios
             WHERE tipo_cuenta IN ('PENSIONADO', 'NO_PENSIONADO')
             ORDER BY activo DESC, apellido, nombre"
        );
        $empleados = $consulta->fetchAll(PDO::FETCH_ASSOC);
        foreach ($empleados as &$empleado) {
            $empleado['id'] = (int) $empleado['id'];
            $empleado['activo'] = (bool) $empleado['activo'];
        }
        unset($empleado);

        return $empleados;
    }

    public function correoUsadoPorOtro(string $correo, int $id): bool
    {
        return $this->existeParaOtro('correo', $correo, $id);
    }

    public function ciUsadoPorOtro(string $ci, int $id): bool
    {
        return $this->existeParaOtro('ci', $ci, $id);
    }

    public function codigoUsadoPorOtro(string $codigo, int $id): bool
    {
        return $this->existeParaOtro('codigo_empleado', $codigo, $id);
    }

    public function actualizar(int $id, array $datos): bool
    {
        $consulta = $this->conexion->prepare(
            "UPDATE usuarios SET
                nombre = :nombre, apellido = :apellido, ci = :ci,
                departamento = :departamento, celular = :celular,
                correo = :correo, tipo_cuenta = :tipo_cuenta,
                codigo_empleado = :codigo_empleado,
                contrasena = COALESCE(:contrasena, contrasena)
             WHERE id = :id AND tipo_cuenta IN ('PENSIONADO', 'NO_PENSIONADO')"
        );
        $consulta->execute([
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'ci' => $datos['ci'],
            'departamento' => $datos['departamento'],
            'celular' => $datos['celular'],
            'correo' => $datos['correo'],
            'tipo_cuenta' => $datos['tipo_cuenta'],
            'codigo_empleado' => $datos['codigo_empleado'],
            'contrasena' => $datos['contrasena'] ?? null,
            'id' => $id,
        ]);

        return $consulta->rowCount() > 0 || $this->empleadoAdministrableExiste($id);
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $consulta = $this->conexion->prepare(
            "UPDATE usuarios SET activo = :activo_nuevo
             WHERE id = :id AND tipo_cuenta IN ('PENSIONADO', 'NO_PENSIONADO') AND activo <> :activo_actual"
        );
        $consulta->execute(['activo_nuevo' => (int) $activo, 'activo_actual' => (int) $activo, 'id' => $id]);

        return $consulta->rowCount() > 0;
    }

    private function existe(string $campo, string $valor): bool
    {
        $consulta = $this->conexion->prepare("SELECT 1 FROM usuarios WHERE {$campo} = :valor LIMIT 1");
        $consulta->execute(['valor' => $valor]);
        return $consulta->fetchColumn() !== false;
    }

    private function existeParaOtro(string $campo, string $valor, int $id): bool
    {
        $consulta = $this->conexion->prepare(
            "SELECT 1 FROM usuarios WHERE {$campo} = :valor AND id <> :id LIMIT 1"
        );
        $consulta->execute(['valor' => $valor, 'id' => $id]);
        return $consulta->fetchColumn() !== false;
    }

    private function empleadoAdministrableExiste(int $id): bool
    {
        $consulta = $this->conexion->prepare(
            "SELECT 1 FROM usuarios WHERE id = :id AND tipo_cuenta IN ('PENSIONADO', 'NO_PENSIONADO') LIMIT 1"
        );
        $consulta->execute(['id' => $id]);
        return $consulta->fetchColumn() !== false;
    }
}
