<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Configuracion;

use PHPUnit\Framework\TestCase;
use Restaurante\Application\Configuracion\ActualizarConfiguracion;
use Restaurante\Application\Configuracion\GuardarDiaCalendario;
use Restaurante\Application\Port\ConfiguracionRepository;
use Restaurante\Domain\Entity\ConfiguracionSistema;
use Restaurante\Domain\Entity\DiaCalendario;

final class ParametrosSistemaTest extends TestCase
{
    public function testGuardaTarifasMinimoYHorarioValidos(): void
    {
        $repo=new ConfiguracionEnMemoria();
        $resultado=(new ActualizarConfiguracion($repo))->ejecutar(['tarifa_pensionado'=>'16.50','tarifa_esporadico'=>'20','minimo_almuerzos_mes'=>'12','hora_limite'=>'08:45']);
        self::assertTrue($resultado->exitoso);
        self::assertSame(16.5,$repo->configuracion?->tarifaPensionado);
        self::assertSame(12,$repo->configuracion?->minimoAlmuerzosMes);
    }

    public function testRechazaTarifaMinimoYHoraInvalidosSinGuardar(): void
    {
        $repo=new ConfiguracionEnMemoria();$caso=new ActualizarConfiguracion($repo);
        self::assertFalse($caso->ejecutar(['tarifa_pensionado'=>'-1','tarifa_esporadico'=>'18','minimo_almuerzos_mes'=>'10','hora_limite'=>'09:00'])->exitoso);
        self::assertFalse($caso->ejecutar(['tarifa_pensionado'=>'15','tarifa_esporadico'=>'18','minimo_almuerzos_mes'=>'32','hora_limite'=>'09:00'])->exitoso);
        self::assertFalse($caso->ejecutar(['tarifa_pensionado'=>'15','tarifa_esporadico'=>'18','minimo_almuerzos_mes'=>'10','hora_limite'=>'25:00'])->exitoso);
        self::assertNull($repo->configuracion);
    }

    public function testRegistraFeriadoYPermiteRestaurarDiaOperativo(): void
    {
        $repo=new ConfiguracionEnMemoria();$caso=new GuardarDiaCalendario($repo);
        self::assertTrue($caso->ejecutar(['fecha'=>'2026-12-25','es_feriado'=>'true','descripcion'=>'Navidad'])->exitoso);
        self::assertTrue($repo->dia?->esFeriado);
        self::assertTrue($caso->ejecutar(['fecha'=>'2026-12-25','es_feriado'=>'false','descripcion'=>''])->exitoso);
        self::assertFalse($repo->dia?->esFeriado);
    }

    public function testRechazaFechaYODescripcionInvalidas(): void
    {
        $repo=new ConfiguracionEnMemoria();$caso=new GuardarDiaCalendario($repo);
        self::assertFalse($caso->ejecutar(['fecha'=>'2026-02-30','es_feriado'=>'true','descripcion'=>'Día'])->exitoso);
        self::assertFalse($caso->ejecutar(['fecha'=>'2026-12-25','es_feriado'=>'true','descripcion'=>str_repeat('x',201)])->exitoso);
        self::assertNull($repo->dia);
    }
}

final class ConfiguracionEnMemoria implements ConfiguracionRepository
{
    public ?ConfiguracionSistema $configuracion=null;
    public ?DiaCalendario $dia=null;
    public function obtener(): array{return $this->configuracion?->aArray()??[];}
    public function guardar(ConfiguracionSistema $configuracion):void{$this->configuracion=$configuracion;}
    public function guardarDia(DiaCalendario $dia):void{$this->dia=$dia;}
    public function listarMes(int $anio,int $mes):array{return [];}
}
