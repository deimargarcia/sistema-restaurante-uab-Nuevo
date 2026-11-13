<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Adapters\Http;

use PHPUnit\Framework\TestCase;
use Restaurante\Adapters\Http\ControladorEmpleados;
use Restaurante\Application\Empleados\RegistrarEmpleado;
use Restaurante\Application\Port\EmpleadoRepository;
use Restaurante\Domain\Entity\Empleado;

final class ControladorEmpleadosTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testNiegaElRegistroSiLaSesionNoEsDeAdministracion(): void
    {
        $repositorio = new RepositorioDePruebaEmpleado();
        $controlador = new ControladorEmpleados(new RegistrarEmpleado($repositorio));

        $respuesta = $controlador->registrar($this->datos());

        self::assertFalse($respuesta['exito']);
        self::assertSame(0, $repositorio->creaciones);
    }

    public function testAdministracionPuedeRegistrarUnEmpleado(): void
    {
        $_SESSION['tipo_cuenta'] = 'ADMIN';
        $repositorio = new RepositorioDePruebaEmpleado();
        $controlador = new ControladorEmpleados(new RegistrarEmpleado($repositorio));

        $respuesta = $controlador->registrar($this->datos());

        self::assertTrue($respuesta['exito']);
        self::assertSame(23, $respuesta['empleado_id']);
        self::assertSame(1, $repositorio->creaciones);
    }

    private function datos(): array
    {
        return [
            'nombre' => 'Ana',
            'apellido' => 'Prueba',
            'correo' => 'persona@uab.bo',
            'contrasena' => 'clave-123',
            'celular' => '70000000',
            'ci' => '12345',
            'departamento' => 'Administración',
            'codigo_empleado' => 'EMP-23',
            'tipo_cuenta' => 'PENSIONADO',
        ];
    }
}

final class RepositorioDePruebaEmpleado implements EmpleadoRepository
{
    public int $creaciones = 0;

    public function correoExiste(string $correo): bool
    {
        return false;
    }

    public function ciExiste(string $ci): bool
    {
        return false;
    }

    public function codigoExiste(string $codigoEmpleado): bool
    {
        return false;
    }

    public function crear(Empleado $empleado): int
    {
        $this->creaciones++;
        return 23;
    }
}
