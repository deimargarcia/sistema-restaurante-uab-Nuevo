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

        $resultado = $this->afiliarBeneficiario->ejecutar($usuarioId, null, $anio, $mes, $usuarioId);
        return [
            'exito' => $resultado->exitoso,
            'mensaje' => $resultado->mensaje,
            'reservas_creadas' => $resultado->reservasCreadas,
        ];
    }
}
