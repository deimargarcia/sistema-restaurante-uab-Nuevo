<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Adapters\Http;

use PHPUnit\Framework\TestCase;
use Restaurante\Adapters\Http\ControladorFamiliares;
use Restaurante\Application\Familiares\RegistrarFamiliar;
use Restaurante\Application\Port\FamiliarRepository;
use Restaurante\Domain\Entity\Familiar;

final class ControladorFamiliaresTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testSoloAdministracionPuedeRegistrarUnFamiliar(): void
    {
        $repositorio = new RepositorioFamiliarHttp();
        $controlador = new ControladorFamiliares(new RegistrarFamiliar($repositorio), $repositorio);

        $respuesta = $controlador->registrar(['titular_id' => 42, 'nombre' => 'Familiar', 'parentesco' => 'HIJO']);

        self::assertFalse($respuesta['exito']);
        self::assertNull($repositorio->guardado);
    }

    public function testAdminPuedeListarTitularesYRegistrarFamiliar(): void
    {
        $_SESSION['tipo_cuenta'] = 'ADMIN';
        $repositorio = new RepositorioFamiliarHttp();
        $controlador = new ControladorFamiliares(new RegistrarFamiliar($repositorio), $repositorio);

        $titulares = $controlador->listarTitulares();
        $respuesta = $controlador->registrar(['titular_id' => 42, 'nombre' => 'Hijo de prueba', 'parentesco' => 'HIJO']);

        self::assertTrue($titulares['exito']);
        self::assertCount(1, $titulares['titulares']);
        self::assertTrue($respuesta['exito']);
        self::assertSame(42, $repositorio->guardado?->titularId);
    }
}

final class RepositorioFamiliarHttp implements FamiliarRepository
{
    public ?Familiar $guardado = null;

    public function listarTitulares(): array
    {
        return [['id' => 42, 'nombre_completo' => 'Titular de prueba', 'codigo_empleado' => 'EMP-42']];
    }

    public function titularEmpleadoExiste(int $titularId): bool
    {
        return $titularId === 42;
    }

    public function crear(Familiar $familiar): int
    {
        $this->guardado = $familiar;
        return 7;
    }
}
