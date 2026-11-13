<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use PDOException;
use Restaurante\Application\Pensionamientos\AfiliacionDuplicada;
use Restaurante\Application\Port\PensionamientoRepository;
use Restaurante\Domain\Entity\Pensionamiento;

final readonly class PdoPensionamientoRepository implements PensionamientoRepository
{
    public function __construct(private PDO $conexion)
    {
    }

    public function tipoCuentaTitular(int $titularId): ?string
    {
        $consulta = $this->conexion->prepare(
            "SELECT tipo_cuenta FROM usuarios
             WHERE id = :id AND activo = 1 AND tipo_cuenta IN ('PENSIONADO', 'NO_PENSIONADO')
             LIMIT 1"
        );
        $consulta->execute(['id' => $titularId]);
        $tipo = $consulta->fetchColumn();
        return $tipo === false ? null : (string) $tipo;
    }

    public function familiarActivoPertenece(int $titularId, int $familiarId): bool
    {
        $consulta = $this->conexion->prepare(
            "SELECT 1 FROM familiares
             WHERE id = :familiar_id AND empleado_titular_id = :titular_id AND estado = 'ACTIVO'
             LIMIT 1"
        );
        $consulta->execute(['familiar_id' => $familiarId, 'titular_id' => $titularId]);
        return $consulta->fetchColumn() !== false;
    }

    public function familiaresActivosDelTitular(int $titularId): array
    {
        $consulta = $this->conexion->prepare(
            "SELECT id, nombre, parentesco FROM familiares
             WHERE empleado_titular_id = :titular_id AND estado = 'ACTIVO'
             ORDER BY nombre"
        );
        $consulta->execute(['titular_id' => $titularId]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function feriadosDelMes(int $anio, int $mes): array
    {
        $inicio = sprintf('%04d-%02d-01', $anio, $mes);
        $fin = (new \DateTimeImmutable($inicio))->format('Y-m-t');
        $consulta = $this->conexion->prepare(
            'SELECT fecha FROM calendario_operativo
             WHERE fecha BETWEEN :inicio AND :fin AND es_feriado = 1'
        );
        $consulta->execute(['inicio' => $inicio, 'fin' => $fin]);
        return array_map('strval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    }

    public function registrarConReservas(Pensionamiento $pensionamiento, int $registradoPorId, array $fechas): int
    {
        $propia = !$this->conexion->inTransaction();
        if ($propia) {
            $this->conexion->beginTransaction();
        } else {
            $this->conexion->exec('SAVEPOINT afiliacion_mensual');
        }

        try {
            $consulta = $this->conexion->prepare(
                'INSERT INTO pensionamientos_mensuales
                    (usuario_id, familiar_id, anio, mes, registrado_por_id)
                 VALUES (:usuario_id, :familiar_id, :anio, :mes, :registrado_por_id)'
            );
            $consulta->execute([
                'usuario_id' => $pensionamiento->titularId,
                'familiar_id' => $pensionamiento->familiarId,
                'anio' => $pensionamiento->anio,
                'mes' => $pensionamiento->mes,
                'registrado_por_id' => $registradoPorId,
            ]);

            $reservasCreadas = $this->crearReservas($pensionamiento, $fechas);

            if ($propia) {
                $this->conexion->commit();
            } else {
                $this->conexion->exec('RELEASE SAVEPOINT afiliacion_mensual');
            }

            return $reservasCreadas;
        } catch (PDOException $error) {
            $this->revertir($propia);
            if ((string) $error->getCode() === '23000' && (int) ($error->errorInfo[1] ?? 0) === 1062) {
                throw new AfiliacionDuplicada('El beneficiario ya está afiliado para ese mes.', previous: $error);
            }
            throw $error;
        } catch (\Throwable $error) {
            $this->revertir($propia);
            throw $error;
        }
    }

    private function crearReservas(Pensionamiento $pensionamiento, array $fechas): int
    {
        $esFamiliar = $pensionamiento->familiarId !== null;
        $sql = $esFamiliar
            ? "INSERT IGNORE INTO reservas
                (usuario_id, familiar_id, tipo_beneficiario, fecha_reserva, estado, modalidad)
               VALUES (:usuario_id, :familiar_id, 'FAMILIAR', :fecha, 'RESERVADO', 'PENSIONADO')"
            : "INSERT IGNORE INTO reservas
                (usuario_id, familiar_id, tipo_beneficiario, fecha_reserva, estado, modalidad)
               VALUES (:usuario_id, NULL, 'TITULAR', :fecha, 'RESERVADO', 'PENSIONADO')";
        $crear = $this->conexion->prepare($sql);
        $nuevas = 0;

        foreach ($fechas as $fecha) {
            $parametros = ['usuario_id' => $pensionamiento->titularId, 'fecha' => $fecha];
            if ($esFamiliar) {
                $parametros['familiar_id'] = $pensionamiento->familiarId;
            }
            $crear->execute($parametros);
            $nuevas += $crear->rowCount();
        }

        $inicio = sprintf('%04d-%02d-01', $pensionamiento->anio, $pensionamiento->mes);
        $fin = (new \DateTimeImmutable($inicio))->format('Y-m-t');
        $condicion = $esFamiliar
            ? 'familiar_id = :beneficiario_id'
            : "usuario_id = :beneficiario_id AND tipo_beneficiario = 'TITULAR'";
        $actualizar = $this->conexion->prepare(
            "UPDATE reservas SET modalidad = 'PENSIONADO'
             WHERE {$condicion} AND fecha_reserva BETWEEN :inicio AND :fin AND estado = 'RESERVADO'"
        );
        $actualizar->execute([
            'beneficiario_id' => $esFamiliar ? $pensionamiento->familiarId : $pensionamiento->titularId,
            'inicio' => $inicio,
            'fin' => $fin,
        ]);

        return $nuevas;
    }

    private function revertir(bool $propia): void
    {
        if ($propia && $this->conexion->inTransaction()) {
            $this->conexion->rollBack();
        } elseif (!$propia) {
            $this->conexion->exec('ROLLBACK TO SAVEPOINT afiliacion_mensual');
            $this->conexion->exec('RELEASE SAVEPOINT afiliacion_mensual');
        }
    }
}
