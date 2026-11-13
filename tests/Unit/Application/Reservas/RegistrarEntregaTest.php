<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Reservas;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Restaurante\Application\Port\ReservaRepository;
use Restaurante\Application\Reservas\RegistrarEntrega;
use Restaurante\Domain\Entity\Reserva;

final class RegistrarEntregaTest extends TestCase
{
    public function testEntregaSoloSeRegistraDespuesDelHorarioLimite(): void
    {
        $repo = new EntregaEnMemoria();
        $temprano = $this->caso($repo, '2026-11-12 08:59:59')->ejecutar(9);
        self::assertFalse($temprano->exitoso);
        self::assertFalse($repo->marcada);

        $aTiempo = $this->caso($repo, '2026-11-12 09:00:00')->ejecutar(9);
        self::assertTrue($aTiempo->exitoso);
        self::assertTrue($repo->marcada);
    }

    public function testSoloMarcaReservaPendienteDeLaFechaActual(): void
    {
        $repo = new EntregaEnMemoria(pendiente: false);
        self::assertFalse($this->caso($repo, '2026-11-12 10:00:00')->ejecutar(9)->exitoso);
    }

    private function caso(EntregaEnMemoria $repo, string $ahora): RegistrarEntrega
    {
        return new RegistrarEntrega($repo, static fn (): DateTimeImmutable => new DateTimeImmutable($ahora));
    }
}

final class EntregaEnMemoria implements ReservaRepository
{
    public bool $marcada = false;
    public function __construct(private readonly bool $pendiente = true) {}
    public function tipoCuentaTitular(int $titularId): ?string { return null; }
    public function familiarActivoPertenece(int $titularId, int $familiarId): bool { return false; }
    public function esFeriado(string $fecha): bool { return false; }
    public function menuPublicado(string $fecha): bool { return false; }
    public function existeAfiliacion(int $titularId, ?int $familiarId, int $anio, int $mes): bool { return false; }
    public function reservaActivaExiste(int $titularId, ?int $familiarId, string $fecha): bool { return false; }
    public function reglas(): array { return ['tarifa_esporadico'=>18.0,'tarifa_pensionado'=>15.0,'hora_limite'=>'09:00:00','minimo_almuerzos_mes'=>10]; }
    public function crear(Reserva $reserva): int { return 0; }
    public function buscarParaCancelar(int $reservaId, int $titularId): ?array { return null; }
    public function contarCobrablesMes(int $titularId, ?int $familiarId, int $anio, int $mes): int { return 0; }
    public function cancelar(int $reservaId): bool { return false; }
    public function actualizarEstadosAutomaticos(string $hoy, string $hora, string $horaLimite): void {}
    public function listarPorTitular(int $titularId): array { return []; }
    public function listarPorFecha(string $fecha): array { return []; }
    public function marcarEntregada(int $reservaId, string $fecha): bool { $this->marcada = $this->pendiente && $fecha === '2026-11-12'; return $this->marcada; }
}
