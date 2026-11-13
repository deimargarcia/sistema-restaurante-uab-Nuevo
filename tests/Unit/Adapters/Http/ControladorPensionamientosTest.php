<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Adapters\Http;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Restaurante\Adapters\Http\ControladorPensionamientos;
use Restaurante\Application\Pensionamientos\AfiliarBeneficiario;
use Restaurante\Application\Port\PensionamientoRepository;
use Restaurante\Domain\Entity\Pensionamiento;

final class ControladorPensionamientosTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testAdminPuedeConsultarTitularesPensionados(): void
    {
        $_SESSION['tipo_cuenta'] = 'ADMIN';
        $controlador = $this->controlador();

        $respuesta = $controlador->listarTitularesPensionados();

        self::assertTrue($respuesta['exito']);
        self::assertCount(1, $respuesta['titulares']);
    }

    public function testAdminPuedeSolicitarAfiliacionParaTitularElegido(): void
    {
        $_SESSION['tipo_cuenta'] = 'ADMIN';
        $repositorio = new RepositorioPensionamientoControlador();
        $controlador = new ControladorPensionamientos(new AfiliarBeneficiario(
            $repositorio,
            static fn (): DateTimeImmutable => new DateTimeImmutable('2026-11-12'),
        ));

        $respuesta = $controlador->afiliarFamiliar(42, 7, 2026, 12, 5);

        self::assertTrue($respuesta['exito']);
        self::assertSame(5, $repositorio->registradoPor);
        self::assertSame(7, $repositorio->afiliacion?->familiarId);
    }

    private function controlador(): ControladorPensionamientos
    {
        return new ControladorPensionamientos(new AfiliarBeneficiario(
            new RepositorioPensionamientoControlador(),
            static fn (): DateTimeImmutable => new DateTimeImmutable('2026-11-12'),
        ));
    }
}

final class RepositorioPensionamientoControlador implements PensionamientoRepository
{
    public ?Pensionamiento $afiliacion = null;
    public ?int $registradoPor = null;

    public function tipoCuentaTitular(int $titularId): ?string { return $titularId === 42 ? 'PENSIONADO' : null; }
    public function familiarActivoPertenece(int $titularId, int $familiarId): bool { return $titularId === 42 && $familiarId === 7; }
    public function familiaresActivosDelTitular(int $titularId): array { return [['id' => 7, 'nombre' => 'Familiar', 'parentesco' => 'HIJO']]; }
    public function titularesPensionados(): array { return [['id' => 42, 'nombre_completo' => 'Titular', 'codigo_empleado' => 'EMP-42']]; }
    public function feriadosDelMes(int $anio, int $mes): array { return []; }
    public function registrarConReservas(Pensionamiento $pensionamiento, int $registradoPorId, array $fechas): int
    {
        $this->afiliacion = $pensionamiento;
        $this->registradoPor = $registradoPorId;
        return count($fechas);
    }
}
