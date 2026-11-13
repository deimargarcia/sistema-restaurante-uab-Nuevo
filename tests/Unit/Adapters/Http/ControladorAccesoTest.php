<?php
declare(strict_types=1);

namespace Restaurante\Tests\Unit\Adapters\Http;

use PHPUnit\Framework\TestCase;
use Restaurante\Adapters\Http\ControladorAcceso;
use Restaurante\Application\Auth\IniciarSesion;
use Restaurante\Application\Port\UsuarioRepository;
use Restaurante\Domain\Entity\Usuario;

final class ControladorAccesoTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testGuardaLaSesionYEnviaAlPanelDelRol(): void
    {
        $usuario = new Usuario(
            14,
            'Admin de prueba',
            'admin@uab.bo',
            'CI-014',
            password_hash('clave-correcta', PASSWORD_DEFAULT),
            'ADMIN',
            null,
            true,
        );
        $controlador = new ControladorAcceso(new IniciarSesion(new RepositorioAcceso($usuario)));

        $respuesta = $controlador->iniciarSesion('admin@uab.bo', 'clave-correcta');

        self::assertTrue($respuesta['exito']);
        self::assertSame('/admin/dashboard.php', $respuesta['destino']);
        self::assertSame(14, $_SESSION['usuario_id']);
        self::assertSame('ADMIN', $_SESSION['tipo_cuenta']);
    }

    public function testUnaCuentaDeEmpleadoRecibeElPanelDeEmpleado(): void
    {
        $usuario = new Usuario(
            15,
            'Empleado de prueba',
            'empleado@uab.bo',
            'CI-015',
            password_hash('clave-correcta', PASSWORD_DEFAULT),
            'PENSIONADO',
            'EMP-015',
            true,
        );
        $controlador = new ControladorAcceso(new IniciarSesion(new RepositorioAcceso($usuario)));

        $respuesta = $controlador->iniciarSesion('empleado@uab.bo', 'clave-correcta');

        self::assertSame('/empleado/dashboard.php', $respuesta['destino']);
        self::assertSame('PENSIONADO', $_SESSION['tipo_cuenta']);
    }

    public function testUnRechazoNoCreaSesion(): void
    {
        $controlador = new ControladorAcceso(new IniciarSesion(new RepositorioAcceso(null)));

        $respuesta = $controlador->iniciarSesion('no-existe@uab.bo', 'clave');

        self::assertFalse($respuesta['exito']);
        self::assertArrayNotHasKey('usuario_id', $_SESSION);
    }
}

final class RepositorioAcceso implements UsuarioRepository
{
    public function __construct(private readonly ?Usuario $usuario)
    {
    }

    public function buscarPorIdentificador(string $correoOCi): ?Usuario
    {
        return $this->usuario;
    }
}
