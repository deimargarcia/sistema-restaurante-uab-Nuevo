<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Pensionamientos\AfiliarBeneficiario;

final readonly class ControladorPensionamientos
{
    public function __construct(private AfiliarBeneficiario $afiliarBeneficiario)
    {
    }

    public function afiliarTitular(int $usuarioId, int $anio, int $mes): array
    {
        if (!in_array($_SESSION['tipo_cuenta'] ?? '', ['PENSIONADO', 'NO_PENSIONADO'], true)) {
            return ['exito' => false, 'mensaje' => 'Solo un empleado puede solicitar su afiliación mensual.'];
        }

        return $this->afiliar($usuarioId, null, $anio, $mes, $usuarioId);
    }

    public function listarFamiliares(int $titularId): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'PENSIONADO') {
            return ['exito' => false, 'mensaje' => 'Solo el titular pensionado puede gestionar a sus familiares.'];
        }

        return [
            'exito' => true,
            'familiares' => $this->afiliarBeneficiario->familiaresActivos($titularId),
        ];
    }

    public function afiliarFamiliar(int $titularId, int $familiarId, int $anio, int $mes): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'PENSIONADO') {
            return ['exito' => false, 'mensaje' => 'Solo un titular pensionado puede afiliar familiares.'];
        }

        return $this->afiliar($titularId, $familiarId, $anio, $mes, $titularId);
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
