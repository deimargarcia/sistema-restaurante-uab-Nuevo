<?php
declare(strict_types=1);

namespace Restaurante\Application\Menus;

use Restaurante\Application\Port\MenuRepository;
use Restaurante\Domain\Entity\Menu;

final readonly class PublicarMenu
{
    public function __construct(private MenuRepository $menus)
    {
    }

    public function ejecutar(array $datos, int $administradorId): ResultadoMenu
    {
        $fecha = trim((string) ($datos['fecha'] ?? ''));
        $fechaObjeto = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaObjeto || $fechaObjeto->format('Y-m-d') !== $fecha) {
            return ResultadoMenu::rechazo('Selecciona una fecha válida.');
        }
        if ((int) $fechaObjeto->format('N') > 4 || $this->menus->esFeriado($fecha)) {
            return ResultadoMenu::rechazo('El menú solo se publica para días de servicio de lunes a jueves.');
        }

        try {
            $menu = new Menu(
                $fecha,
                trim((string) ($datos['sopa'] ?? '')),
                trim((string) ($datos['plato_principal'] ?? '')),
                trim((string) ($datos['postre'] ?? '')),
                $administradorId,
                isset($datos['imagen']) && trim((string) $datos['imagen']) !== '' ? trim((string) $datos['imagen']) : null,
            );
        } catch (\InvalidArgumentException $error) {
            return ResultadoMenu::rechazo($error->getMessage());
        }

        $this->menus->guardar($menu);
        return ResultadoMenu::exito('Menú publicado para el ' . $fechaObjeto->format('d/m/Y') . '.');
    }
}
