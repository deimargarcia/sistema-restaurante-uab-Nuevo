<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use PDOException;
use Restaurante\Application\Empleados\EmpleadoDuplicado;
use Restaurante\Application\Port\EmpleadoRepository;
use Restaurante\Domain\Entity\Empleado;

final readonly class PdoEmpleadoRepository implements EmpleadoRepository
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

    private function existe(string $campo, string $valor): bool
    {
        $consulta = $this->conexion->prepare("SELECT 1 FROM usuarios WHERE {$campo} = :valor LIMIT 1");
        $consulta->execute(['valor' => $valor]);
        return $consulta->fetchColumn() !== false;
    }
}
