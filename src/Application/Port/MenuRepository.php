<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

use Restaurante\Domain\Entity\Menu;

interface MenuRepository
{
    public function esFeriado(string $fecha): bool;
    public function guardar(Menu $menu): void;
    /** @return list<array<string,mixed>> */
    public function listarSemana(string $lunes): array;
    /** @return list<array<string,mixed>> */
    public function listarTodos(int $limite = 60): array;
}
