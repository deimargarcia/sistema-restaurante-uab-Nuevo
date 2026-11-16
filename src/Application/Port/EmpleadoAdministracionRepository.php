<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

interface EmpleadoAdministracionRepository
{
    /** @return list<array<string, mixed>> */
    public function listarAdministrables(): array;

    public function correoUsadoPorOtro(string $correo, int $id): bool;

    public function ciUsadoPorOtro(string $ci, int $id): bool;

    public function codigoUsadoPorOtro(string $codigo, int $id): bool;

    /** @param array<string, string> $datos */
    public function actualizar(int $id, array $datos): bool;

    public function cambiarEstado(int $id, bool $activo): bool;
}
