<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Familiares\ActualizarFamiliar;
use Restaurante\Application\Familiares\CambiarEstadoFamiliar;
use Restaurante\Application\Familiares\ListarFamiliares;

final readonly class ControladorGestionFamiliares
{
    public function __construct(
        private ListarFamiliares $listarFamiliares,
        private ActualizarFamiliar $actualizarFamiliar,
        private CambiarEstadoFamiliar $cambiarEstadoFamiliar,
    ) {
    }

    public function listar(): array
    {
        if (!$this->esAdministracion()) {
            return ['exito' => false, 'mensaje' => 'Solo administración puede gestionar familiares.'];
        }

        return ['exito' => true, 'familiares' => $this->listarFamiliares->ejecutar()];
    }

    public function actualizar(int $id, array $datos): array
    {
        if (!$this->esAdministracion()) {
            return ['exito' => false, 'mensaje' => 'Solo administración puede modificar familiares.'];
        }

        $resultado = $this->actualizarFamiliar->ejecutar($id, $datos);
        return ['exito' => $resultado->exitoso, 'mensaje' => $resultado->mensaje];
    }

    public function cambiarEstado(int $id, string $estado): array
    {
        if (!$this->esAdministracion()) {
            return ['exito' => false, 'mensaje' => 'Solo administración puede cambiar el estado de familiares.'];
        }

        $resultado = $this->cambiarEstadoFamiliar->ejecutar($id, $estado);
        return ['exito' => $resultado->exitoso, 'mensaje' => $resultado->mensaje];
    }

    private function esAdministracion(): bool
    {
        return ($_SESSION['tipo_cuenta'] ?? '') === 'ADMIN';
    }
}
