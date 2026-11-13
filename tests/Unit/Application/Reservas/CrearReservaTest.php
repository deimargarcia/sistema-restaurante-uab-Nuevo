<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Reservas;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Restaurante\Application\Port\ReservaRepository;
use Restaurante\Application\Reservas\CrearReserva;
use Restaurante\Domain\Entity\Reserva;

final class CrearReservaTest extends TestCase
{
    public function testCreaReservaEsporadicaConTarifaConfigurada(): void
    {
        $repo = new ReservaEnMemoria();
        $resultado = $this->caso($repo)->ejecutar(42, null, '2026-11-12');

        self::assertTrue($resultado->exitoso);
        self::assertSame(18.0, $resultado->tarifa);
        self::assertSame('ESPORADICO', $repo->guardada?->modalidad);
    }

    public function testUtilizaTarifaMensualCuandoBeneficiarioEstaAfiliado(): void
    {
        $repo = new ReservaEnMemoria(afiliado: true);
        $resultado = $this->caso($repo)->ejecutar(42, null, '2026-11-12');

        self::assertTrue($resultado->exitoso);
        self::assertSame(15.0, $resultado->tarifa);
        self::assertSame('PENSIONADO', $repo->guardada?->modalidad);
    }

    public function testRechazaFechaPasadaFinDeSemanaFeriadoYFueraDeHorario(): void
    {
        self::assertFalse($this->caso(new ReservaEnMemoria())->ejecutar(42, null, '2026-11-11')->exitoso);
        self::assertFalse($this->caso(new ReservaEnMemoria())->ejecutar(42, null, '2026-11-13')->exitoso);
        self::assertFalse($this->caso(new ReservaEnMemoria(feriado: true))->ejecutar(42, null, '2026-11-12')->exitoso);
        self::assertFalse($this->caso(new ReservaEnMemoria(), '2026-11-12 09:00:00')->ejecutar(42, null, '2026-11-12')->exitoso);
    }

    public function testExigeMenuPublicadoCuentaValidaYFamiliarVinculado(): void
    {
        self::assertFalse($this->caso(new ReservaEnMemoria(menu: false))->ejecutar(42, null, '2026-11-12')->exitoso);
        self::assertFalse($this->caso(new ReservaEnMemoria(tipo: null))->ejecutar(42, null, '2026-11-12')->exitoso);
        self::assertFalse($this->caso(new ReservaEnMemoria(tipo: 'NO_PENSIONADO', familiar: true))->ejecutar(42, 7, '2026-11-12')->exitoso);
        self::assertFalse($this->caso(new ReservaEnMemoria())->ejecutar(42, 9, '2026-11-12')->exitoso);
    }

    public function testRechazaReservaActivaDuplicada(): void
    {
        self::assertFalse($this->caso(new ReservaEnMemoria(duplicada: true))->ejecutar(42, null, '2026-11-12')->exitoso);
    }

    private function caso(ReservaEnMemoria $repo, string $ahora = '2026-11-12 08:30:00'): CrearReserva
    {
        return new CrearReserva($repo, static fn (): DateTimeImmutable => new DateTimeImmutable($ahora));
    }
}

final class ReservaEnMemoria implements ReservaRepository
{
    public ?Reserva $guardada = null;

    public function __construct(
        private readonly ?string $tipo = 'NO_PENSIONADO',
        private readonly bool $familiar = false,
        private readonly bool $feriado = false,
        private readonly bool $menu = true,
        private readonly bool $afiliado = false,
        private readonly bool $duplicada = false,
    ) {
    }

    public function tipoCuentaTitular(int $titularId): ?string { return $this->tipo; }
    public function familiarActivoPertenece(int $titularId, int $familiarId): bool { return $this->familiar; }
    public function esFeriado(string $fecha): bool { return $this->feriado; }
    public function menuPublicado(string $fecha): bool { return $this->menu; }
    public function existeAfiliacion(int $titularId, ?int $familiarId, int $anio, int $mes): bool { return $this->afiliado; }
    public function reservaActivaExiste(int $titularId, ?int $familiarId, string $fecha): bool { return $this->duplicada; }
    public function reglas(): array { return ['tarifa_esporadico' => 18.0, 'tarifa_pensionado' => 15.0, 'hora_limite' => '09:00:00', 'minimo_almuerzos_mes' => 10]; }
    public function crear(Reserva $reserva): int { $this->guardada = $reserva; return 81; }
    public function buscarParaCancelar(int $reservaId, int $titularId): ?array { return null; }
    public function contarCobrablesMes(int $titularId, ?int $familiarId, int $anio, int $mes): int { return 0; }
    public function cancelar(int $reservaId): bool { return false; }
    public function actualizarEstadosAutomaticos(string $hoy, string $hora, string $horaLimite): void {}
    public function listarPorTitular(int $titularId): array { return []; }
    public function listarPorFecha(string $fecha): array { return []; }
}
