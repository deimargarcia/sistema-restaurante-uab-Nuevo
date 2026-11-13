<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Auth\IniciarSesion;

final readonly class ControladorAcceso
{
    public function __construct(
        private IniciarSesion $iniciarSesion,
        private string $basePublica = '',
    ) {
    }

    public function iniciarSesion(string $correoOCi, string $contrasena): array
    {
        $resultado = $this->iniciarSesion->ejecutar($correoOCi, $contrasena);

        if (!$resultado->exitoso) {
            return ['exito' => false, 'mensaje' => $resultado->mensaje];
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $resultado->usuarioId;
        $_SESSION['nombre'] = $resultado->nombreCompleto;
        $_SESSION['tipo_cuenta'] = $resultado->tipoCuenta;
        $_SESSION['codigo_empleado'] = $resultado->codigoEmpleado;

        $panel = $resultado->tipoCuenta === 'ADMIN'
            ? '/admin/dashboard.php'
            : '/empleado/dashboard.php';

        return [
            'exito' => true,
            'mensaje' => 'Bienvenido/a, ' . $resultado->nombreCompleto,
            'destino' => rtrim($this->basePublica, '/') . $panel,
        ];
    }
}
