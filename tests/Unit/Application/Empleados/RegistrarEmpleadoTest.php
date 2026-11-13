<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Empleados;

use PHPUnit\Framework\TestCase;
use Restaurante\Application\Empleados\RegistrarEmpleado;
use Restaurante\Application\Port\EmpleadoRepository;
use Restaurante\Domain\Entity\Empleado;

final class RegistrarEmpleadoTest extends TestCase
{
    public function testRegistraEmpleadoNormalizandoDatosYGuardandoHash(): void
    {
        $repositorio = new RepositorioEmpleadoEnMemoria();
        $resultado = (new RegistrarEmpleado($repositorio))->ejecutar($this->datos([
            'correo' => '  PERSONA@UAB.BO ',
            'ci' => ' 12 345 ',
            'codigo_empleado' => ' emp-12 ',
            'tipo_cuenta' => 'pensionado',
        ]));

        self::assertTrue($resultado->exitoso);
        self::assertSame(1, $resultado->empleadoId);
        self::assertSame('persona@uab.bo', $repositorio->guardado?->correo);
        self::assertSame('12345', $repositorio->guardado?->ci);
        self::assertSame('EMP-12', $repositorio->guardado?->codigoEmpleado);
        self::assertSame('PENSIONADO', $repositorio->guardado?->tipoCuenta);
        self::assertTrue(password_verify('clave-123', $repositorio->guardado?->contrasenaHash ?? ''));
    }

    public function testPideLosCamposObligatoriosQueFaltan(): void
    {
        $repositorio = new RepositorioEmpleadoEnMemoria();
        $resultado = (new RegistrarEmpleado($repositorio))->ejecutar([]);

        self::assertFalse($resultado->exitoso);
        self::assertSame('El campo nombre es obligatorio.', $resultado->mensaje);
        self::assertNull($repositorio->guardado);
    }

    public function testRechazaCorreoDuplicado(): void
    {
        $repositorio = new RepositorioEmpleadoEnMemoria(correos: ['persona@uab.bo']);
        $resultado = (new RegistrarEmpleado($repositorio))->ejecutar($this->datos());

        self::assertFalse($resultado->exitoso);
        self::assertSame('El correo ya está registrado.', $resultado->mensaje);
    }

    public function testRechazaCiDuplicado(): void
    {
        $repositorio = new RepositorioEmpleadoEnMemoria(cis: ['12345']);
        $resultado = (new RegistrarEmpleado($repositorio))->ejecutar($this->datos());

        self::assertFalse($resultado->exitoso);
        self::assertSame('El CI ya está registrado.', $resultado->mensaje);
    }

    public function testRechazaCodigoDeEmpleadoDuplicado(): void
    {
        $repositorio = new RepositorioEmpleadoEnMemoria(codigos: ['EMP-12']);
        $resultado = (new RegistrarEmpleado($repositorio))->ejecutar($this->datos());

        self::assertFalse($resultado->exitoso);
        self::assertSame('El código de empleado ya está en uso.', $resultado->mensaje);
    }

    public function testRechazaDatosConFormatoOTipoDeCuentaNoPermitido(): void
    {
        $caso = new RegistrarEmpleado(new RepositorioEmpleadoEnMemoria());

        $correoInvalido = $caso->ejecutar($this->datos(['correo' => 'correo-invalido']));
        $tipoInvalido = $caso->ejecutar($this->datos(['tipo_cuenta' => 'ADMIN']));

        self::assertFalse($correoInvalido->exitoso);
        self::assertSame('Ingresa un correo electrónico válido.', $correoInvalido->mensaje);
        self::assertFalse($tipoInvalido->exitoso);
        self::assertSame('El tipo de cuenta no es válido.', $tipoInvalido->mensaje);
    }

    private function datos(array $cambios = []): array
    {
        return array_replace([
            'nombre' => 'Ana',
            'apellido' => 'Prueba',
            'correo' => 'persona@uab.bo',
            'contrasena' => 'clave-123',
            'celular' => '70000000',
            'ci' => '12345',
            'departamento' => 'Administración',
            'codigo_empleado' => 'EMP-12',
            'tipo_cuenta' => 'NO_PENSIONADO',
        ], $cambios);
    }
}

final class RepositorioEmpleadoEnMemoria implements EmpleadoRepository
{
    public ?Empleado $guardado = null;

    public function __construct(
        private readonly array $correos = [],
        private readonly array $cis = [],
        private readonly array $codigos = [],
    ) {
    }

    public function correoExiste(string $correo): bool
    {
        return in_array($correo, $this->correos, true);
    }

    public function ciExiste(string $ci): bool
    {
        return in_array($ci, $this->cis, true);
    }

    public function codigoExiste(string $codigoEmpleado): bool
    {
        return in_array($codigoEmpleado, $this->codigos, true);
    }

    public function crear(Empleado $empleado): int
    {
        $this->guardado = $empleado;
        return 1;
    }
}
