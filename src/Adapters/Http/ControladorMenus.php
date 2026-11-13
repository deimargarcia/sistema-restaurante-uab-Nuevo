<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Menus\ConsultarMenuSemana;
use Restaurante\Application\Menus\PublicarMenu;

final readonly class ControladorMenus
{
    public function __construct(private PublicarMenu $publicarMenu, private ConsultarMenuSemana $consultarMenu)
    {
    }

    public function publicar(array $datos, int $administradorId): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
            return ['exito' => false, 'mensaje' => 'Solo administración puede publicar menús.'];
        }
        $resultado = $this->publicarMenu->ejecutar($datos, $administradorId);
        return ['exito' => $resultado->exitoso, 'mensaje' => $resultado->mensaje];
    }

    public function consultarSemana(?string $fecha = null): array
    {
        if (!in_array($_SESSION['tipo_cuenta'] ?? '', ['ADMIN', 'PENSIONADO', 'NO_PENSIONADO'], true)) {
            return ['exito' => false, 'mensaje' => 'Inicia sesión para consultar el menú.'];
        }
        return ['exito' => true, 'menus' => $this->consultarMenu->ejecutar($fecha)];
    }
}
