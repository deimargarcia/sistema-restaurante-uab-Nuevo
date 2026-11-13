<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Menus;

use PHPUnit\Framework\TestCase;
use Restaurante\Application\Menus\ConsultarMenuSemana;
use Restaurante\Application\Menus\PublicarMenu;
use Restaurante\Application\Port\MenuRepository;
use Restaurante\Domain\Entity\Menu;

final class PublicarMenuTest extends TestCase
{
    public function testPublicaLosTresPlatosParaDiaOperativo(): void
    {
        $repo = new MenuEnMemoria();
        $resultado = (new PublicarMenu($repo))->ejecutar($this->datos('2026-11-16'), 5);
        self::assertTrue($resultado->exitoso);
        self::assertSame('Sopa', $repo->guardado?->sopa);
        self::assertSame('Carne', $repo->guardado?->platoPrincipal);
        self::assertSame('Fruta', $repo->guardado?->postre);
        self::assertSame(5, $repo->guardado?->publicadoPorId);
    }

    public function testRechazaFechaInvalidaFinDeSemanaYFeriado(): void
    {
        $servicio = new PublicarMenu(new MenuEnMemoria());
        self::assertFalse($servicio->ejecutar($this->datos('2026-02-30'), 5)->exitoso);
        self::assertFalse($servicio->ejecutar($this->datos('2026-11-13'), 5)->exitoso);
        $repoFeriado = new MenuEnMemoria(feriado: true);
        self::assertFalse((new PublicarMenu($repoFeriado))->ejecutar($this->datos('2026-11-16'), 5)->exitoso);
        self::assertSame('2026-11-16', $repoFeriado->fechaConsultadaComoFeriado);
        self::assertNull($repoFeriado->guardado);
    }

    public function testRechazaPlatosIncompletosYImagenInvalida(): void
    {
        $repo = new MenuEnMemoria();
        $servicio = new PublicarMenu($repo);
        self::assertFalse($servicio->ejecutar(['fecha'=>'2026-11-16','sopa'=>'','plato_principal'=>'Carne','postre'=>'Fruta'], 5)->exitoso);
        self::assertFalse($servicio->ejecutar($this->datos('2026-11-16', 'no-url'), 5)->exitoso);
        self::assertNull($repo->guardado);
    }

    public function testConsultaLosMenusDeLaSemanaDesdeElLunesCalculado(): void
    {
        $repo = new MenuEnMemoria();
        $repo->menusSemana = [['fecha' => '2026-11-16'], ['fecha' => '2026-11-17']];
        $semana = (new ConsultarMenuSemana($repo))->ejecutar('2026-11-18');
        self::assertSame('2026-11-16', $repo->inicioSemana);
        self::assertSame($repo->menusSemana, $semana);
    }

    private function datos(string $fecha, string $imagen = ''): array
    {
        return ['fecha'=>$fecha,'sopa'=>'Sopa','plato_principal'=>'Carne','postre'=>'Fruta','imagen'=>$imagen];
    }
}

final class MenuEnMemoria implements MenuRepository
{
    public ?Menu $guardado = null;
    public ?string $inicioSemana = null;
    public ?string $fechaConsultadaComoFeriado = null;
    public array $menusSemana = [];
    public function __construct(private readonly bool $feriado = false) {}
    public function esFeriado(string $fecha): bool { $this->fechaConsultadaComoFeriado = $fecha; return $this->feriado; }
    public function guardar(Menu $menu): void { $this->guardado = $menu; }
    public function listarSemana(string $lunes): array { $this->inicioSemana = $lunes; return $this->menusSemana; }
    public function listarTodos(int $limite = 60): array { return []; }
}
