<?php
declare(strict_types=1);

namespace Restaurante\Application\Auth;

use Restaurante\Application\Port\UsuarioRepository;

final readonly class IniciarSesion
{
    public function __construct(private UsuarioRepository $usuarios)
    {
    }

    public function ejecutar(string $correo, string $contrasena): ResultadoInicioSesion
    {
        $correo = mb_strtolower(trim($correo));

        if ($correo === '' || $contrasena === '') {
            return ResultadoInicioSesion::rechazo('Completa todos los campos.');
        }

        $usuario = $this->usuarios->buscarPorCorreo($correo);
        if ($usuario === null || !$usuario->verificarContrasena($contrasena)) {
            return ResultadoInicioSesion::rechazo('Credenciales incorrectas.');
        }

        if (!$usuario->activo) {
            return ResultadoInicioSesion::rechazo('Tu cuenta está suspendida o inactiva. Contacta al administrador.');
        }

        return ResultadoInicioSesion::exito(
            $usuario->id,
            $usuario->nombreCompleto,
            $usuario->tipoCuenta,
            $usuario->codigoEmpleado,
        );
    }
}
