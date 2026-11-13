<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use Restaurante\Application\Port\ReservaRepository;
use Restaurante\Domain\Entity\Reserva;

final readonly class PdoReservaRepository implements ReservaRepository
{
    public function __construct(private PDO $conexion)
    {
    }

    public function tipoCuentaTitular(int $titularId): ?string
    {
        $consulta = $this->conexion->prepare(
            "SELECT tipo_cuenta FROM usuarios WHERE id = :id AND activo = 1 AND tipo_cuenta IN ('PENSIONADO','NO_PENSIONADO') LIMIT 1"
        );
        $consulta->execute(['id' => $titularId]);
        $tipo = $consulta->fetchColumn();
        return $tipo === false ? null : (string) $tipo;
    }

    public function familiarActivoPertenece(int $titularId, int $familiarId): bool
    {
        $consulta = $this->conexion->prepare(
            "SELECT 1 FROM familiares WHERE id = :familiar AND empleado_titular_id = :titular AND estado = 'ACTIVO' LIMIT 1"
        );
        $consulta->execute(['familiar' => $familiarId, 'titular' => $titularId]);
        return $consulta->fetchColumn() !== false;
    }

    public function esFeriado(string $fecha): bool
    {
        $consulta = $this->conexion->prepare('SELECT es_feriado FROM calendario_operativo WHERE fecha = :fecha LIMIT 1');
        $consulta->execute(['fecha' => $fecha]);
        return (bool) $consulta->fetchColumn();
    }

    public function menuPublicado(string $fecha): bool
    {
        $consulta = $this->conexion->prepare('SELECT 1 FROM menus WHERE fecha = :fecha AND publicado = 1 LIMIT 1');
        $consulta->execute(['fecha' => $fecha]);
        return $consulta->fetchColumn() !== false;
    }

    public function existeAfiliacion(int $titularId, ?int $familiarId, int $anio, int $mes): bool
    {
        $consulta = $this->conexion->prepare(
            'SELECT 1 FROM pensionamientos_mensuales
             WHERE usuario_id = :titular AND familiar_id <=> :familiar AND anio = :anio AND mes = :mes LIMIT 1'
        );
        $consulta->execute(['titular' => $titularId, 'familiar' => $familiarId, 'anio' => $anio, 'mes' => $mes]);
        return $consulta->fetchColumn() !== false;
    }

    public function reservaActivaExiste(int $titularId, ?int $familiarId, string $fecha): bool
    {
        $condicion = $familiarId === null ? "tipo_beneficiario = 'TITULAR'" : 'familiar_id = :familiar';
        $consulta = $this->conexion->prepare(
            "SELECT 1 FROM reservas WHERE usuario_id = :titular AND {$condicion}
             AND fecha_reserva = :fecha AND estado <> 'CANCELADO' LIMIT 1"
        );
        $parametros = ['titular' => $titularId, 'fecha' => $fecha];
        if ($familiarId !== null) {
            $parametros['familiar'] = $familiarId;
        }
        $consulta->execute($parametros);
        return $consulta->fetchColumn() !== false;
    }

    public function reglas(): array
    {
        $fila = $this->conexion->query(
            'SELECT tarifa_esporadico, tarifa_pensionado, hora_limite, minimo_almuerzos_mes
             FROM configuracion_sistema WHERE id = 1'
        )->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            return ['tarifa_esporadico' => 18.0, 'tarifa_pensionado' => 15.0, 'hora_limite' => '09:00:00', 'minimo_almuerzos_mes' => 10];
        }
        return [
            'tarifa_esporadico' => (float) $fila['tarifa_esporadico'],
            'tarifa_pensionado' => (float) $fila['tarifa_pensionado'],
            'hora_limite' => (string) $fila['hora_limite'],
            'minimo_almuerzos_mes' => (int) $fila['minimo_almuerzos_mes'],
        ];
    }

    public function crear(Reserva $reserva): int
    {
        $consulta = $this->conexion->prepare(
            "INSERT INTO reservas (usuario_id, familiar_id, tipo_beneficiario, fecha_reserva, estado, modalidad)
             VALUES (:titular, :familiar, :tipo, :fecha, 'RESERVADO', :modalidad)"
        );
        $consulta->execute([
            'titular' => $reserva->titularId,
            'familiar' => $reserva->familiarId,
            'tipo' => $reserva->familiarId === null ? 'TITULAR' : 'FAMILIAR',
            'fecha' => $reserva->fecha,
            'modalidad' => $reserva->modalidad,
        ]);
        return (int) $this->conexion->lastInsertId();
    }

    public function buscarParaCancelar(int $reservaId, int $titularId): ?array
    {
        $consulta = $this->conexion->prepare(
            'SELECT id, usuario_id, familiar_id, tipo_beneficiario, fecha_reserva, estado, modalidad
             FROM reservas WHERE id = :reserva AND usuario_id = :titular LIMIT 1'
        );
        $consulta->execute(['reserva' => $reservaId, 'titular' => $titularId]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    public function contarCobrablesMes(int $titularId, ?int $familiarId, int $anio, int $mes): int
    {
        $inicio = sprintf('%04d-%02d-01', $anio, $mes);
        $fin = (new \DateTimeImmutable($inicio))->format('Y-m-t');
        $beneficiario = $familiarId === null
            ? "usuario_id = :beneficiario AND tipo_beneficiario = 'TITULAR'"
            : 'familiar_id = :beneficiario';
        $consulta = $this->conexion->prepare(
            "SELECT COUNT(*) FROM reservas WHERE {$beneficiario} AND fecha_reserva BETWEEN :inicio AND :fin
             AND estado <> 'CANCELADO'"
        );
        $consulta->execute(['beneficiario' => $familiarId ?? $titularId, 'inicio' => $inicio, 'fin' => $fin]);
        return (int) $consulta->fetchColumn();
    }

    public function cancelar(int $reservaId): bool
    {
        $consulta = $this->conexion->prepare(
            "UPDATE reservas SET estado = 'CANCELADO', fecha_cancelacion = NOW()
             WHERE id = :id AND estado = 'RESERVADO'"
        );
        $consulta->execute(['id' => $reservaId]);
        return $consulta->rowCount() === 1;
    }

    public function actualizarEstadosAutomaticos(string $hoy, string $hora, string $horaLimite): void
    {
        $pasadas = $this->conexion->prepare(
            "UPDATE reservas SET estado = 'COBRADO_INASISTENCIA'
             WHERE fecha_reserva < :hoy AND estado IN ('RESERVADO', 'PENDIENTE_ENTREGA')"
        );
        $pasadas->execute(['hoy' => $hoy]);
        if ($hora >= $horaLimite) {
            $hoyPendientes = $this->conexion->prepare(
                "UPDATE reservas SET estado = 'PENDIENTE_ENTREGA'
                 WHERE fecha_reserva = :hoy AND estado = 'RESERVADO'"
            );
            $hoyPendientes->execute(['hoy' => $hoy]);
        }
    }

    public function listarPorTitular(int $titularId): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT r.*, f.nombre AS nombre_familiar FROM reservas r
             LEFT JOIN familiares f ON f.id = r.familiar_id
             WHERE r.usuario_id = :titular ORDER BY r.fecha_reserva DESC, r.id DESC'
        );
        $consulta->execute(['titular' => $titularId]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorFecha(string $fecha): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT r.*, CONCAT_WS(\' \', NULLIF(u.nombre, \'\'), NULLIF(u.apellido, \'\')) AS nombre_titular,
                    u.codigo_empleado, f.nombre AS nombre_familiar
             FROM reservas r JOIN usuarios u ON u.id = r.usuario_id
             LEFT JOIN familiares f ON f.id = r.familiar_id
             WHERE r.fecha_reserva = :fecha ORDER BY u.apellido, u.nombre, r.tipo_beneficiario'
        );
        $consulta->execute(['fecha' => $fecha]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarEntregada(int $reservaId, string $fecha): bool
    {
        $consulta = $this->conexion->prepare(
            "UPDATE reservas SET estado='ENTREGADO'
             WHERE id=:id AND fecha_reserva=:fecha AND estado='PENDIENTE_ENTREGA'"
        );
        $consulta->execute(['id' => $reservaId, 'fecha' => $fecha]);
        return $consulta->rowCount() === 1;
    }
}
