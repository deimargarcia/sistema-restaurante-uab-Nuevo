<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Repository;

use PDO;
use Restaurante\Application\Port\MenuRepository;
use Restaurante\Domain\Entity\Menu;

final readonly class PdoMenuRepository implements MenuRepository
{
    public function __construct(private PDO $conexion)
    {
    }

    public function esFeriado(string $fecha): bool
    {
        $consulta = $this->conexion->prepare('SELECT es_feriado FROM calendario_operativo WHERE fecha = :fecha LIMIT 1');
        $consulta->execute(['fecha' => $fecha]);
        return (bool) $consulta->fetchColumn();
    }

    public function guardar(Menu $menu): void
    {
        $consulta = $this->conexion->prepare(
            "INSERT INTO menus (fecha,sopa,plato_principal,postre,descripcion_menu,imagen_path,publicado,created_by)
             VALUES (:fecha,:sopa,:principal,:postre,'',:imagen,1,:admin)
             ON DUPLICATE KEY UPDATE sopa=VALUES(sopa), plato_principal=VALUES(plato_principal),
               postre=VALUES(postre), imagen_path=VALUES(imagen_path), publicado=1, created_by=VALUES(created_by)"
        );
        $consulta->execute([
            'fecha' => $menu->fecha,
            'sopa' => $menu->sopa,
            'principal' => $menu->platoPrincipal,
            'postre' => $menu->postre,
            'imagen' => $menu->imagen,
            'admin' => $menu->publicadoPorId,
        ]);
    }

    public function listarSemana(string $lunes): array
    {
        $fin = (new \DateTimeImmutable($lunes))->modify('+6 days')->format('Y-m-d');
        $consulta = $this->conexion->prepare(
            'SELECT fecha,sopa,plato_principal,postre,imagen_path FROM menus
             WHERE fecha BETWEEN :inicio AND :fin AND publicado=1 ORDER BY fecha'
        );
        $consulta->execute(['inicio' => $lunes, 'fin' => $fin]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTodos(int $limite = 60): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT m.id,m.fecha,m.sopa,m.plato_principal,m.postre,m.imagen_path,m.publicado,
                    CONCAT_WS(\' \',NULLIF(u.nombre,\'\'),NULLIF(u.apellido,\'\')) AS publicado_por
             FROM menus m JOIN usuarios u ON u.id=m.created_by ORDER BY m.fecha DESC LIMIT :limite'
        );
        $consulta->bindValue('limite', max(1, min($limite, 100)), PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}
