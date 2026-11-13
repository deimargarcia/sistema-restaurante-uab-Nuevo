<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Adapters\Http;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Restaurante\Adapters\Http\ControladorReservas;
use Restaurante\Application\Port\ReservaRepository;
use Restaurante\Application\Reservas\CancelarReserva;
use Restaurante\Application\Reservas\CrearReserva;
use Restaurante\Application\Reservas\ListarReservasDia;
use Restaurante\Application\Reservas\ListarReservasTitular;
use Restaurante\Application\Reservas\RegistrarEntrega;
use Restaurante\Domain\Entity\Reserva;

final class ControladorReservasTest extends TestCase
{
    protected function setUp(): void { $_SESSION = []; }

    public function testAdministradorNoPuedeCrearReservaComoBeneficiario(): void
    {
        $_SESSION['tipo_cuenta'] = 'ADMIN';
        $repo = new ReservaControladorEnMemoria();
        $respuesta = $this->controlador($repo)->crear(10, null, '2026-11-12');
        self::assertFalse($respuesta['exito']);
        self::assertNull($repo->creada);
    }

    public function testEmpleadoPuedeCrearReservaYAdministracionConsultarConsolidado(): void
    {
        $repo = new ReservaControladorEnMemoria();
        $_SESSION['tipo_cuenta'] = 'NO_PENSIONADO';
        $respuesta = $this->controlador($repo)->crear(42, null, '2026-11-12');
        self::assertTrue($respuesta['exito']);

        $_SESSION['tipo_cuenta'] = 'ADMIN';
        $consolidado = $this->controlador($repo)->listarDia('2026-11-12');
        self::assertTrue($consolidado['exito']);
    }

    private function controlador(ReservaControladorEnMemoria $repo): ControladorReservas
    {
        $reloj = static fn (): DateTimeImmutable => new DateTimeImmutable('2026-11-12 08:30:00');
        return new ControladorReservas(new CrearReserva($repo, $reloj), new CancelarReserva($repo, $reloj), new ListarReservasTitular($repo, $reloj), new ListarReservasDia($repo, $reloj), new RegistrarEntrega($repo, $reloj));
    }
}

final class ReservaControladorEnMemoria implements ReservaRepository
{
    public ?Reserva $creada = null;
    public function tipoCuentaTitular(int $titularId): ?string { return 'NO_PENSIONADO'; }
    public function familiarActivoPertenece(int $titularId, int $familiarId): bool { return false; }
    public function esFeriado(string $fecha): bool { return false; }
    public function menuPublicado(string $fecha): bool { return true; }
    public function existeAfiliacion(int $titularId, ?int $familiarId, int $anio, int $mes): bool { return false; }
    public function reservaActivaExiste(int $titularId, ?int $familiarId, string $fecha): bool { return false; }
    public function reglas(): array { return ['tarifa_esporadico' => 18.0, 'tarifa_pensionado' => 15.0, 'hora_limite' => '09:00:00', 'minimo_almuerzos_mes' => 10]; }
    public function crear(Reserva $reserva): int { $this->creada = $reserva; return 1; }
    public function buscarParaCancelar(int $reservaId, int $titularId): ?array { return null; }
    public function contarCobrablesMes(int $titularId, ?int $familiarId, int $anio, int $mes): int { return 0; }
    public function cancelar(int $reservaId): bool { return false; }
    public function actualizarEstadosAutomaticos(string $hoy, string $hora, string $horaLimite): void {}
    public function listarPorTitular(int $titularId): array { return []; }
    public function listarPorFecha(string $fecha): array { return []; }
    public function marcarEntregada(int $reservaId, string $fecha): bool { return false; }
}
