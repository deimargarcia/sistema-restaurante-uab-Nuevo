<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Familiares;

use PHPUnit\Framework\TestCase;
use Restaurante\Application\Familiares\RegistrarFamiliar;
use Restaurante\Application\Port\FamiliarRepository;
use Restaurante\Domain\Entity\Familiar;

final class RegistrarFamiliarTest extends TestCase
{
    public function testVinculaFamiliarAlTitularSeleccionado(): void
    {
        $repositorio = new RepositorioFamiliarEnMemoria();
        $resultado = (new RegistrarFamiliar($repositorio))->ejecutar(42, '  María Pérez ', 'madre', ' 12 345 ');

        self::assertTrue($resultado->exitoso);
        self::assertSame(7, $resultado->familiarId);
        self::assertSame(42, $repositorio->guardado?->titularId);
        self::assertSame('María Pérez', $repositorio->guardado?->nombre);
        self::assertSame('MADRE', $repositorio->guardado?->parentesco);
        self::assertSame('12345', $repositorio->guardado?->ci);
    }

    public function testAceptaPadresConyugeEHijos(): void
    {
        $caso = new RegistrarFamiliar(new RepositorioFamiliarEnMemoria());

        foreach (['PADRE', 'MADRE', 'CONYUGE', 'HIJO', 'HIJA'] as $parentesco) {
            self::assertTrue($caso->ejecutar(42, 'Familiar de prueba', $parentesco, 'CI-' . $parentesco)->exitoso);
        }
    }

    public function testRechazaUnParentescoFueraDelPrimerGrado(): void
    {
        $repositorio = new RepositorioFamiliarEnMemoria();
        $resultado = (new RegistrarFamiliar($repositorio))->ejecutar(42, 'Luis Pérez', 'TIO', '12345');

        self::assertFalse($resultado->exitoso);
        self::assertSame('Solo se permite registrar padres, cónyuge e hijos.', $resultado->mensaje);
        self::assertNull($repositorio->guardado);
    }

    public function testExigeUnEmpleadoTitularExistente(): void
    {
        $repositorio = new RepositorioFamiliarEnMemoria(titularExiste: false);
        $resultado = (new RegistrarFamiliar($repositorio))->ejecutar(42, 'María Pérez', 'MADRE', '12345');

        self::assertFalse($resultado->exitoso);
        self::assertNull($repositorio->guardado);
    }

    public function testRechazaNombreVacio(): void
    {
        $repositorio = new RepositorioFamiliarEnMemoria();
        $resultado = (new RegistrarFamiliar($repositorio))->ejecutar(42, '  ', 'HIJO', '12345');

        self::assertFalse($resultado->exitoso);
        self::assertNull($repositorio->guardado);
    }
}

final class RepositorioFamiliarEnMemoria implements FamiliarRepository
{
    public ?Familiar $guardado = null;

    public function __construct(private readonly bool $titularExiste = true)
    {
    }

    public function titularEmpleadoExiste(int $titularId): bool
    {
        return $this->titularExiste && $titularId === 42;
    }

    public function ciExiste(string $ci): bool
    {
        return false;
    }

    public function listarTitulares(): array
    {
        return [];
    }

    public function crear(Familiar $familiar): int
    {
        $this->guardado = $familiar;
        return 7;
    }
}
