<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use Restaurante\Application\Port\ReporteRepository;

final readonly class PdoReporteRepository implements ReporteRepository
{
    public function __construct(private PDO $conexion)
    {
    }

    public function reglas(): array
    {
        $fila = $this->conexion->query(
            'SELECT tarifa_pensionado,tarifa_esporadico,minimo_almuerzos_mes,hora_limite
             FROM configuracion_sistema WHERE id=1'
        )->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'tarifa_pensionado' => (float) ($fila['tarifa_pensionado'] ?? 15),
            'tarifa_esporadico' => (float) ($fila['tarifa_esporadico'] ?? 18),
            'minimo_almuerzos_mes' => (int) ($fila['minimo_almuerzos_mes'] ?? 10),
            'hora_limite' => (string) ($fila['hora_limite'] ?? '09:00:00'),
        ];
    }

    public function actualizarEstados(string $hoy, string $hora, string $horaLimite): void
    {
        (new PdoReservaRepository($this->conexion))->actualizarEstadosAutomaticos($hoy, $hora, $horaLimite);
    }

    public function datosDelMes(int $anio, int $mes): array
    {
        $inicio = sprintf('%04d-%02d-01', $anio, $mes);
        $fin = (new \DateTimeImmutable($inicio))->format('Y-m-t');
        $condicionCobro = "r.estado IN ('PENDIENTE_ENTREGA','ENTREGADO','COBRADO_INASISTENCIA')";
        $titulares = $this->conexion->prepare(
            "SELECT u.id, CONCAT_WS(' ',NULLIF(u.nombre,''),NULLIF(u.apellido,'')) AS nombre,
                    u.codigo_empleado,
                    SUM(r.modalidad='PENSIONADO') AS almuerzos_pensionados,
                    SUM(r.modalidad='ESPORADICO') AS almuerzos_esporadicos,
                    EXISTS(SELECT 1 FROM pensionamientos_mensuales p WHERE p.usuario_id=u.id
                           AND p.familiar_id IS NULL AND p.anio=:anio_afiliacion AND p.mes=:mes_afiliacion) AS afiliado
             FROM usuarios u LEFT JOIN reservas r ON r.usuario_id=u.id AND r.tipo_beneficiario='TITULAR'
               AND r.fecha_reserva BETWEEN :inicio AND :fin AND {$condicionCobro}
             WHERE u.activo=1 AND u.tipo_cuenta IN ('PENSIONADO','NO_PENSIONADO')
             GROUP BY u.id ORDER BY u.apellido,u.nombre"
        );
        $titulares->execute(['anio_afiliacion' => $anio, 'mes_afiliacion' => $mes, 'inicio' => $inicio, 'fin' => $fin]);
        $filas = $titulares->fetchAll(PDO::FETCH_ASSOC);

        $familiares = $this->conexion->prepare(
            "SELECT f.empleado_titular_id AS titular_id,f.id,f.nombre,
                    SUM(r.modalidad='PENSIONADO') AS almuerzos_pensionados,
                    SUM(r.modalidad='ESPORADICO') AS almuerzos_esporadicos,
                    EXISTS(SELECT 1 FROM pensionamientos_mensuales p WHERE p.usuario_id=f.empleado_titular_id
                           AND p.familiar_id=f.id AND p.anio=:anio_afiliacion AND p.mes=:mes_afiliacion) AS afiliado
             FROM familiares f LEFT JOIN reservas r ON r.familiar_id=f.id
               AND r.fecha_reserva BETWEEN :inicio AND :fin AND {$condicionCobro}
             WHERE EXISTS(SELECT 1 FROM pensionamientos_mensuales p2 WHERE p2.usuario_id=f.empleado_titular_id
                          AND p2.familiar_id=f.id AND p2.anio=:anio_incluido AND p2.mes=:mes_incluido)
                OR EXISTS(SELECT 1 FROM reservas r2 WHERE r2.familiar_id=f.id
                          AND r2.fecha_reserva BETWEEN :inicio_reservas AND :fin_reservas
                          AND r2.estado IN ('PENDIENTE_ENTREGA','ENTREGADO','COBRADO_INASISTENCIA'))
             GROUP BY f.id ORDER BY f.nombre"
        );
        $familiares->execute([
            'anio_afiliacion' => $anio, 'mes_afiliacion' => $mes,
            'inicio' => $inicio, 'fin' => $fin,
            'anio_incluido' => $anio, 'mes_incluido' => $mes,
            'inicio_reservas' => $inicio, 'fin_reservas' => $fin,
        ]);
        $porTitular = [];
        foreach ($familiares->fetchAll(PDO::FETCH_ASSOC) as $familiar) {
            $porTitular[(int) $familiar['titular_id']][] = [
                'id' => (int) $familiar['id'],
                'nombre' => (string) $familiar['nombre'],
                'almuerzos_pensionados' => (int) $familiar['almuerzos_pensionados'],
                'almuerzos_esporadicos' => (int) $familiar['almuerzos_esporadicos'],
                'afiliado' => (bool) $familiar['afiliado'],
            ];
        }
        foreach ($filas as &$titular) {
            $titular['almuerzos_pensionados'] = (int) $titular['almuerzos_pensionados'];
            $titular['almuerzos_esporadicos'] = (int) $titular['almuerzos_esporadicos'];
            $titular['afiliado'] = (bool) $titular['afiliado'];
            $titular['familiares'] = $porTitular[(int) $titular['id']] ?? [];
        }
        unset($titular);
        return $filas;
    }

    public function guardar(array $fila): void
    {
        $consulta = $this->conexion->prepare(
            'INSERT INTO reportes_mensuales
             (anio,mes,codigo_empleado,nombre_empleado,almuerzos_titular,monto_titular,detalle_familiares,
              almuerzos_familiares,penalizacion_familiares,total_descuento)
             VALUES (:anio,:mes,:codigo,:nombre,:almuerzos,:monto,:detalle,:almuerzos_familiares,:penalizacion,:total)
             ON DUPLICATE KEY UPDATE nombre_empleado=VALUES(nombre_empleado),
              almuerzos_titular=VALUES(almuerzos_titular),monto_titular=VALUES(monto_titular),
              detalle_familiares=VALUES(detalle_familiares),almuerzos_familiares=VALUES(almuerzos_familiares),
              penalizacion_familiares=VALUES(penalizacion_familiares),total_descuento=VALUES(total_descuento),
              generado_en=CURRENT_TIMESTAMP'
        );
        $consulta->execute([
            'anio' => $fila['anio'], 'mes' => $fila['mes'], 'codigo' => $fila['codigo_empleado'],
            'nombre' => $fila['nombre_empleado'], 'almuerzos' => $fila['almuerzos_titular'],
            'monto' => $fila['monto_titular'],
            'detalle' => json_encode($fila['detalle_familiares'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'almuerzos_familiares' => $fila['almuerzos_familiares'],
            'penalizacion' => $fila['penalizacion_familiares'], 'total' => $fila['total_descuento'],
        ]);
    }

    public function listar(int $anio, int $mes): array
    {
        $consulta = $this->conexion->prepare('SELECT * FROM reportes_mensuales WHERE anio=:anio AND mes=:mes ORDER BY nombre_empleado');
        $consulta->execute(['anio' => $anio, 'mes' => $mes]);
        $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as &$fila) {
            $fila['detalle_familiares'] = json_decode((string) ($fila['detalle_familiares'] ?? '[]'), true) ?: [];
            foreach (['almuerzos_titular','almuerzos_familiares'] as $campo) $fila[$campo] = (int) $fila[$campo];
            foreach (['monto_titular','penalizacion_familiares','total_descuento'] as $campo) $fila[$campo] = (float) $fila[$campo];
        }
        unset($fila);
        return $filas;
    }
}
