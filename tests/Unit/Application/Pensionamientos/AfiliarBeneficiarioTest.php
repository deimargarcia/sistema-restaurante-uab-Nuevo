<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Pensionamientos;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Restaurante\Application\Pensionamientos\AfiliacionDuplicada;
use Restaurante\Application\Pensionamientos\AfiliarBeneficiario;
use Restaurante\Application\Port\PensionamientoRepository;
use Restaurante\Domain\Entity\Pensionamiento;

final class AfiliarBeneficiarioTest extends TestCase
{
    public function testAfiliacionDelTitularCreaReservasDelMesFuturo(): void
    {
        $repositorio = new RepositorioPensionamientoEnMemoria();
        $caso = $this->caso($repositorio);

        $resultado = $caso->ejecutar(42, null, 2026, 12, 42);

        self::assertTrue($resultado->exitoso);
        self::assertGreaterThan(0, $resultado->reservasCreadas);
        self::assertSame(42, $repositorio->guardado?->titularId);
        self::assertNull($repositorio->guardado?->familiarId);
    }

    public function testProgramaSoloLunesAJuevesYOmiteFeriados(): void
    {
        $repositorio = new RepositorioPensionamientoEnMemoria(feriados: ['2026-12-01']);
        $caso = $this->caso($repositorio);
        $resultado = $caso->ejecutar(42, null, 2026, 12, 42);

        self::assertTrue($resultado->exitoso);
        self::assertNotContains('2026-12-01', $repositorio->fechas);
        foreach ($repositorio->fechas as $fecha) {
            self::assertLessThanOrEqual(4, (int) (new DateTimeImmutable($fecha))->format('N'));
        }
    }

    public function testNoPermiteAfiliarAlMesActualNiAUnMesPasado(): void
    {
        $caso = $this->caso(new RepositorioPensionamientoEnMemoria());

        self::assertFalse($caso->ejecutar(42, null, 2026, 11, 42)->exitoso);
        self::assertFalse($caso->ejecutar(42, null, 2026, 10, 42)->exitoso);
    }

    public function testRechazaCuentaNoDisponibleYPeriodoInvalido(): void
    {
        $repositorio = new RepositorioPensionamientoEnMemoria(tipoCuenta: null);
        $caso = $this->caso($repositorio);

        self::assertFalse($caso->ejecutar(42, null, 2026, 12, 42)->exitoso);
        self::assertFalse($caso->ejecutar(42, null, 2026, 13, 42)->exitoso);
    }

    public function testNoDuplicaUnaAfiliacionDelMismoBeneficiario(): void
    {
        $repositorio = new RepositorioPensionamientoEnMemoria(duplicada: true);
        $resultado = $this->caso($repositorio)->ejecutar(42, null, 2026, 12, 42);

        self::assertFalse($resultado->exitoso);
        self::assertSame('El beneficiario ya está afiliado para ese mes.', $resultado->mensaje);
    }

    public function testTitularPensionadoPuedeAfiliarFamiliarActivo(): void
    {
        $repositorio = new RepositorioPensionamientoEnMemoria(tipoCuenta: 'PENSIONADO', familiarVinculado: true);
        $resultado = $this->caso($repositorio)->ejecutar(42, 7, 2026, 12, 42);

        self::assertTrue($resultado->exitoso);
        self::assertSame(7, $repositorio->guardado?->familiarId);
    }

    public function testNoPensionadoNoPuedeAfiliarFamiliares(): void
    {
        $repositorio = new RepositorioPensionamientoEnMemoria(tipoCuenta: 'NO_PENSIONADO', familiarVinculado: true);
        $resultado = $this->caso($repositorio)->ejecutar(42, 7, 2026, 12, 42);

        self::assertFalse($resultado->exitoso);
        self::assertNull($repositorio->guardado);
    }

    public function testFamiliarDebeEstarActivoYVinculadoAlTitular(): void
    {
        $repositorio = new RepositorioPensionamientoEnMemoria(tipoCuenta: 'PENSIONADO');
        $resultado = $this->caso($repositorio)->ejecutar(42, 99, 2026, 12, 42);

        self::assertFalse($resultado->exitoso);
        self::assertNull($repositorio->guardado);
    }

    private function caso(RepositorioPensionamientoEnMemoria $repositorio): AfiliarBeneficiario
    {
        return new AfiliarBeneficiario(
            $repositorio,
            static fn (): DateTimeImmutable => new DateTimeImmutable('2026-11-12'),
        );
    }
}

final class RepositorioPensionamientoEnMemoria implements PensionamientoRepository
{
    public ?Pensionamiento $guardado = null;
    public array $fechas = [];

    public function __construct(
        private readonly ?string $tipoCuenta = 'NO_PENSIONADO',
        private readonly array $feriados = [],
        private readonly bool $duplicada = false,
        private readonly bool $familiarVinculado = false,
    ) {
    }

    public function tipoCuentaTitular(int $titularId): ?string
    {
        return $this->tipoCuenta;
    }

    public function familiarActivoPertenece(int $titularId, int $familiarId): bool
    {
        return $this->familiarVinculado && $titularId === 42 && $familiarId === 7;
    }

    public function familiaresActivosDelTitular(int $titularId): array
    {
        return $titularId === 42 && $this->tipoCuenta === 'PENSIONADO' ? [['id' => 7, 'nombre' => 'Familiar', 'parentesco' => 'HIJO']] : [];
    }

    public function feriadosDelMes(int $anio, int $mes): array
    {
        return $this->feriados;
    }

    public function registrarConReservas(Pensionamiento $pensionamiento, int $registradoPorId, array $fechas): int
    {
        if ($this->duplicada) {
            throw new AfiliacionDuplicada('El beneficiario ya está afiliado para ese mes.');
        }
        $this->guardado = $pensionamiento;
        $this->fechas = $fechas;
        return count($fechas);
    }
}
