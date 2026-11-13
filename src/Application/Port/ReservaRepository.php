<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

use Restaurante\Domain\Entity\Reserva;

interface ReservaRepository
{
    public function tipoCuentaTitular(int $titularId): ?string;
    public function familiarActivoPertenece(int $titularId, int $familiarId): bool;
    public function esFeriado(string $fecha): bool;
    public function menuPublicado(string $fecha): bool;
    public function existeAfiliacion(int $titularId, ?int $familiarId, int $anio, int $mes): bool;
    public function reservaActivaExiste(int $titularId, ?int $familiarId, string $fecha): bool;
    /** @return array{tarifa_esporadico:float,tarifa_pensionado:float,hora_limite:string,minimo_almuerzos_mes:int} */
    public function reglas(): array;
    public function crear(Reserva $reserva): int;
    public function buscarParaCancelar(int $reservaId, int $titularId): ?array;
    public function contarCobrablesMes(int $titularId, ?int $familiarId, int $anio, int $mes): int;
    public function cancelar(int $reservaId): bool;
    public function actualizarEstadosAutomaticos(string $hoy, string $hora, string $horaLimite): void;
    /** @return list<array<string,mixed>> */
    public function listarPorTitular(int $titularId): array;
    /** @return list<array<string,mixed>> */
    public function listarPorFecha(string $fecha): array;
}
