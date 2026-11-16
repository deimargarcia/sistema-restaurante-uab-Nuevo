<?php
declare(strict_types=1);

namespace Restaurante\Application\Familiares;

use Restaurante\Application\Port\AdministracionFamiliarRepository;

final readonly class CambiarEstadoFamiliar
{
    public function __construct(private AdministracionFamiliarRepository $familiares) {}

    public function ejecutar(int $id, string $estado): ResultadoGestionFamiliar
    {
        $estado = strtoupper(trim($estado));
        if ($id < 1 || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            return ResultadoGestionFamiliar::rechazo('Selecciona un familiar y estado válidos.');
        }
        if (!$this->familiares->cambiarEstado($id, $estado)) {
            return ResultadoGestionFamiliar::rechazo('No se encontró el familiar seleccionado o ya tenía ese estado.');
        }

        return ResultadoGestionFamiliar::exito($estado === 'ACTIVO' ? 'Familiar reactivado.' : 'Familiar desactivado.');
    }
}
