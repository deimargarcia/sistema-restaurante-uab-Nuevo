<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Familiares\RegistrarFamiliar;
use Restaurante\Application\Port\FamiliarRepository;

final readonly class ControladorFamiliares
{
    public function __construct(
        private RegistrarFamiliar $registrarFamiliar,
        private FamiliarRepository $familiares,
    ) {
    }

    public function listarTitulares(): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
            return ['exito' => false, 'mensaje' => 'Solo administración puede gestionar familiares.'];
        }

        return ['exito' => true, 'titulares' => $this->familiares->listarTitulares()];
    }

    public function registrar(array $datos): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
            return ['exito' => false, 'mensaje' => 'Solo administración puede gestionar familiares.'];
        }

        $resultado = $this->registrarFamiliar->ejecutar(
            (int) ($datos['titular_id'] ?? 0),
            (string) ($datos['nombre'] ?? ''),
            (string) ($datos['parentesco'] ?? ''),
        );

        return [
            'exito' => $resultado->exitoso,
            'mensaje' => $resultado->mensaje,
            'familiar_id' => $resultado->familiarId,
        ];
    }
}
