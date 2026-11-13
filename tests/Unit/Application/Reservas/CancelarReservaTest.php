<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Reservas;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Restaurante\Application\Port\ReservaRepository;
use Restaurante\Application\Reservas\CancelarReserva;
use Restaurante\Domain\Entity\Reserva;

final class CancelarReservaTest extends TestCase
{
    public function testCancelaReservaPropiaAntesDelLimite(): void
    {
        $repo = new ReservaCancelacionEnMemoria();
        $resultado = $this->caso($repo)->ejecutar(5, 42);

        self::assertTrue($resultado->exitoso);
        self::assertSame(5, $repo->cancelada);
        self::assertSame('2026-11-12', $repo->ultimaFechaSincronizada);
    }

    public function testNoCancelaReservaAjenaInexistenteNiYaCerrada(): void
    {
        self::assertFalse($this->caso(new ReservaCancelacionEnMemoria(reserva: false))->ejecutar(5, 42)->exitoso);
        self::assertFalse($this->caso(new ReservaCancelacionEnMemoria(estado: 'ENTREGADO'))->ejecutar(5, 42)->exitoso);
    }

    public function testNoPermiteCancelarReservaDeHoyDespuesDeLasNueve(): void
    {
        $resultado = $this->caso(new ReservaCancelacionEnMemoria(), '2026-11-12 09:00:00')->ejecutar(5, 42);
        self::assertFalse($resultado->exitoso);
    }

    public function testReservaMensualNoPuedeBajarDelMinimo(): void
    {
        $repo = new ReservaCancelacionEnMemoria(modalidad: 'PENSIONADO', cobrables: 10);
        $resultado = $this->caso($repo)->ejecutar(5, 42);
        self::assertFalse($resultado->exitoso);
        self::assertNull($repo->cancelada);
    }

    public function testReservaEsporadicaNoEstaSujetaAlMinimoMensual(): void
    {
        $repo = new ReservaCancelacionEnMemoria(modalidad: 'ESPORADICO', cobrables: 1);
        $resultado = $this->caso($repo)->ejecutar(5, 42);
        self::assertTrue($resultado->exitoso);
    }

    private function caso(ReservaCancelacionEnMemoria $repo, string $ahora = '2026-11-12 08:30:00'): CancelarReserva
    {
        return new CancelarReserva($repo, static fn (): DateTimeImmutable => new DateTimeImmutable($ahora));
    }
}

final class ReservaCancelacionEnMemoria implements ReservaRepository
{
    public ?int $cancelada = null;
    public ?string $ultimaFechaSincronizada = null;

    public function __construct(
        private readonly ?string $estado = 'RESERVADO',
        private readonly string $modalidad = 'PENSIONADO',
        private readonly int $cobrables = 11,
        private readonly bool $reserva = true,
    ) {
    }

    public function tipoCuentaTitular(int $titularId): ?string { return null; }
    public function familiarActivoPertenece(int $titularId, int $familiarId): bool { return false; }
    public function esFeriado(string $fecha): bool { return false; }
    public function menuPublicado(string $fecha): bool { return false; }
    public function existeAfiliacion(int $titularId, ?int $familiarId, int $anio, int $mes): bool { return false; }
    public function reservaActivaExiste(int $titularId, ?int $familiarId, string $fecha): bool { return false; }
    public function reglas(): array { return ['tarifa_esporadico' => 18.0, 'tarifa_pensionado' => 15.0, 'hora_limite' => '09:00:00', 'minimo_almuerzos_mes' => 10]; }
    public function crear(Reserva $reserva): int { return 0; }
    public function buscarParaCancelar(int $reservaId, int $titularId): ?array
    {
        if (!$this->reserva || $titularId !== 42) return null;
        return ['estado' => $this->estado, 'fecha_reserva' => '2026-11-12', 'modalidad' => $this->modalidad, 'tipo_beneficiario' => 'TITULAR', 'familiar_id' => null];
    }
    public function contarCobrablesMes(int $titularId, ?int $familiarId, int $anio, int $mes): int { return $this->cobrables; }
    public function cancelar(int $reservaId): bool { $this->cancelada = $reservaId; return true; }
    public function actualizarEstadosAutomaticos(string $hoy, string $hora, string $horaLimite): void { $this->ultimaFechaSincronizada = $hoy; }
    public function listarPorTitular(int $titularId): array { return []; }
    public function listarPorFecha(string $fecha): array { return []; }
    public function marcarEntregada(int $reservaId, string $fecha): bool { return false; }
}
