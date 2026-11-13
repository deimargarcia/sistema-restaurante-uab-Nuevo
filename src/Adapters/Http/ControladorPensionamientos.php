<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Pensionamientos\AfiliarBeneficiario;

final readonly class ControladorPensionamientos
{
    public function __construct(private AfiliarBeneficiario $afiliarBeneficiario)
    {
    }

    public function afiliarTitular(int $usuarioId, int $anio, int $mes, ?int $registradoPorId = null): array
    {
        if (!in_array($_SESSION['tipo_cuenta'] ?? '', ['PENSIONADO', 'NO_PENSIONADO', 'ADMIN'], true)) {
            return ['exito' => false, 'mensaje' => 'No tienes permisos para registrar la afiliación.'];
        }

        return $this->afiliar($usuarioId, null, $anio, $mes, $registradoPorId ?? $usuarioId);
    }

    public function listarFamiliares(int $titularId): array
    {
        if (!in_array($_SESSION['tipo_cuenta'] ?? '', ['PENSIONADO', 'ADMIN'], true)) {
            return ['exito' => false, 'mensaje' => 'No tienes permisos para consultar familiares.'];
        }

        return [
            'exito' => true,
            'familiares' => $this->afiliarBeneficiario->familiaresActivos($titularId),
        ];
    }

    public function afiliarFamiliar(int $titularId, int $familiarId, int $anio, int $mes, ?int $registradoPorId = null): array
    {
        if (!in_array($_SESSION['tipo_cuenta'] ?? '', ['PENSIONADO', 'ADMIN'], true)) {
            return ['exito' => false, 'mensaje' => 'No tienes permisos para afiliar familiares.'];
        }

        return $this->afiliar($titularId, $familiarId, $anio, $mes, $registradoPorId ?? $titularId);
    }

    public function listarTitularesPensionados(): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
            return ['exito' => false, 'mensaje' => 'Solo administración puede consultar los titulares.'];
        }

        return ['exito' => true, 'titulares' => $this->afiliarBeneficiario->titularesPensionados()];
    }

    private function afiliar(int $usuarioId, ?int $familiarId, int $anio, int $mes, int $registradoPorId): array
    {
        $resultado = $this->afiliarBeneficiario->ejecutar($usuarioId, $familiarId, $anio, $mes, $registradoPorId);
        return [
            'exito' => $resultado->exitoso,
            'mensaje' => $resultado->mensaje,
            'reservas_creadas' => $resultado->reservasCreadas,
        ];
    }
}
