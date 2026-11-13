<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Application\Auth;

use PHPUnit\Framework\TestCase;
use Restaurante\Application\Auth\IniciarSesion;
use Restaurante\Domain\Entity\Usuario;
use Restaurante\Application\Port\UsuarioRepository;

final class IniciarSesionTest extends TestCase
{
    public function testCredencialesCorrectasInicianSesion(): void
    {
        $caso = new IniciarSesion(new RepositorioUsuarioEnMemoria($this->usuario()));

        $resultado = $caso->ejecutar(' ADMIN@UAB.BO ', 'clave-correcta');

        self::assertTrue($resultado->exitoso);
        self::assertSame(10, $resultado->usuarioId);
        self::assertSame('ADMIN', $resultado->tipoCuenta);
    }

    public function testPermiteIniciarSesionConCi(): void
    {
        $repositorio = new RepositorioUsuarioEnMemoria($this->usuario());
        $caso = new IniciarSesion($repositorio);

        $resultado = $caso->ejecutar(' ci-001 ', 'clave-correcta');

        self::assertTrue($resultado->exitoso);
        self::assertSame('CI-001', $repositorio->ultimoIdentificador);
    }

    public function testContrasenaIncorrectaSeRechaza(): void
    {
        $caso = new IniciarSesion(new RepositorioUsuarioEnMemoria($this->usuario()));

        $resultado = $caso->ejecutar('admin@uab.bo', 'incorrecta');

        self::assertFalse($resultado->exitoso);
        self::assertSame('Credenciales incorrectas.', $resultado->mensaje);
    }

    public function testCuentaInactivaNoPuedeIniciarSesion(): void
    {
        $caso = new IniciarSesion(new RepositorioUsuarioEnMemoria($this->usuario(activo: false)));

        $resultado = $caso->ejecutar('admin@uab.bo', 'clave-correcta');

        self::assertFalse($resultado->exitoso);
        self::assertStringContainsString('inactiva', $resultado->mensaje);
    }

    public function testCamposVacíosSeRechazanAntesDeConsultar(): void
    {
        $repositorio = new RepositorioUsuarioEnMemoria(null);
        $caso = new IniciarSesion($repositorio);

        $resultado = $caso->ejecutar('', '');

        self::assertFalse($resultado->exitoso);
        self::assertSame(0, $repositorio->busquedas);
    }

    private function usuario(bool $activo = true): Usuario
    {
        return new Usuario(10, 'Admin Prueba', 'admin@uab.bo', 'CI-001', password_hash('clave-correcta', PASSWORD_DEFAULT), 'ADMIN', null, $activo);
    }
}

final class RepositorioUsuarioEnMemoria implements UsuarioRepository
{
    public int $busquedas = 0;
    public ?string $ultimoIdentificador = null;

    public function __construct(private readonly ?Usuario $usuario)
    {
    }

    public function buscarPorIdentificador(string $correoOCi): ?Usuario
    {
        $this->busquedas++;
        $this->ultimoIdentificador = $correoOCi;
        if ($this->usuario === null) {
            return null;
        }

        return strtolower($this->usuario->correo) === strtolower($correoOCi)
            || strtoupper($this->usuario->ci ?? '') === strtoupper($correoOCi)
                ? $this->usuario
                : null;
    }
}
