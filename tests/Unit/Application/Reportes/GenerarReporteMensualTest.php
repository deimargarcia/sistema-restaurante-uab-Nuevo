<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Reportes;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Restaurante\Application\Port\ReporteRepository;
use Restaurante\Application\Reportes\GenerarReporteMensual;

final class GenerarReporteMensualTest extends TestCase
{
    public function testCalculaMinimoTarifasPenalizacionFamiliarYTotal(): void
    {
        $repo = new ReporteEnMemoria();
        $respuesta = $this->caso($repo)->ejecutar(2026, 10);

        self::assertTrue($respuesta['exito']);
        self::assertSame(168.0, $respuesta['filas'][0]['monto_titular']);
        self::assertSame(60.0, $respuesta['filas'][0]['penalizacion_familiares']);
        self::assertSame(336.0, $respuesta['filas'][0]['total_descuento']);
        self::assertSame($respuesta['filas'][0], $repo->guardado[0]);
    }

    public function testSoloPermiteGenerarPeriodosCerradosYValidos(): void
    {
        $caso = $this->caso(new ReporteEnMemoria());
        self::assertFalse($caso->ejecutar(2026, 11)['exito']);
        self::assertFalse($caso->ejecutar(2026, 13)['exito']);
        self::assertTrue($caso->ejecutar(2026, 10)['exito']);
    }

    private function caso(ReporteEnMemoria $repo): GenerarReporteMensual
    {
        return new GenerarReporteMensual($repo, static fn (): DateTimeImmutable => new DateTimeImmutable('2026-11-13 10:00:00'));
    }
}

final class ReporteEnMemoria implements ReporteRepository
{
    public array $guardado = [];
    public ?array $sincronizacion = null;
    public function reglas(): array { return ['tarifa_pensionado'=>15.0,'tarifa_esporadico'=>18.0,'minimo_almuerzos_mes'=>10,'hora_limite'=>'09:00:00']; }
    public function actualizarEstados(string $hoy,string $hora,string $horaLimite): void { $this->sincronizacion=[$hoy,$hora,$horaLimite]; }
    public function datosDelMes(int $anio,int $mes): array { return [[
        'codigo_empleado'=>'EMP-42','nombre'=>'Empleado de prueba','afiliado'=>true,'almuerzos_pensionados'=>8,'almuerzos_esporadicos'=>1,
        'familiares'=>[['nombre'=>'Familiar de prueba','afiliado'=>true,'almuerzos_pensionados'=>6,'almuerzos_esporadicos'=>1]],
    ]]; }
    public function guardar(array $fila): void { $this->guardado[]=$fila; }
    public function listar(int $anio,int $mes): array { return $this->guardado; }
}
