<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use Restaurante\Application\Port\ConfiguracionRepository;
use Restaurante\Domain\Entity\ConfiguracionSistema;
use Restaurante\Domain\Entity\DiaCalendario;

final readonly class PdoConfiguracionRepository implements ConfiguracionRepository
{
    public function __construct(private PDO $conexion) {}

    public function obtener(): array
    {
        $fila = $this->conexion->query('SELECT tarifa_pensionado,tarifa_esporadico,minimo_almuerzos_mes,hora_limite FROM configuracion_sistema WHERE id=1')->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'tarifa_pensionado' => (float) ($fila['tarifa_pensionado'] ?? 15),
            'tarifa_esporadico' => (float) ($fila['tarifa_esporadico'] ?? 18),
            'minimo_almuerzos_mes' => (int) ($fila['minimo_almuerzos_mes'] ?? 10),
            'hora_limite' => substr((string) ($fila['hora_limite'] ?? '09:00:00'), 0, 5),
        ];
    }

    public function guardar(ConfiguracionSistema $configuracion): void
    {
        $datos = $configuracion->aArray();
        $consulta = $this->conexion->prepare(
            'INSERT INTO configuracion_sistema (id,tarifa_pensionado,tarifa_esporadico,minimo_almuerzos_mes,hora_limite)
             VALUES (1,:pensionado,:esporadico,:minimo,:hora)
             ON DUPLICATE KEY UPDATE tarifa_pensionado=VALUES(tarifa_pensionado),
              tarifa_esporadico=VALUES(tarifa_esporadico),minimo_almuerzos_mes=VALUES(minimo_almuerzos_mes),hora_limite=VALUES(hora_limite)'
        );
        $consulta->execute([
            'pensionado' => $datos['tarifa_pensionado'],
            'esporadico' => $datos['tarifa_esporadico'],
            'minimo' => $datos['minimo_almuerzos_mes'],
            'hora' => $datos['hora_limite'] . ':00',
        ]);
    }

    public function guardarDia(DiaCalendario $dia): void
    {
        $consulta = $this->conexion->prepare(
            'INSERT INTO calendario_operativo (fecha,es_feriado,descripcion_feriado)
             VALUES (:fecha,:feriado,:descripcion)
             ON DUPLICATE KEY UPDATE es_feriado=VALUES(es_feriado),descripcion_feriado=VALUES(descripcion_feriado)'
        );
        $consulta->execute([
            'fecha' => $dia->fecha,
            'feriado' => (int) $dia->esFeriado,
            'descripcion' => $dia->esFeriado ? (trim($dia->descripcion) ?: null) : null,
        ]);
    }

    public function listarMes(int $anio, int $mes): array
    {
        $inicio = sprintf('%04d-%02d-01', $anio, $mes);
        $fin = (new \DateTimeImmutable($inicio))->format('Y-m-t');
        $consulta = $this->conexion->prepare(
            'SELECT id,fecha,es_feriado,descripcion_feriado FROM calendario_operativo
             WHERE fecha BETWEEN :inicio AND :fin ORDER BY fecha'
        );
        $consulta->execute(['inicio' => $inicio, 'fin' => $fin]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}
