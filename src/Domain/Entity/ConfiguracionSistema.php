<?php
declare(strict_types=1);

namespace Restaurante\Domain\Entity;

final readonly class ConfiguracionSistema
{
    public function __construct(
        public float $tarifaPensionado,
        public float $tarifaEsporadico,
        public int $minimoAlmuerzosMes,
        public string $horaLimite,
    ) {
        foreach ([$tarifaPensionado, $tarifaEsporadico] as $tarifa) {
            if (!is_finite($tarifa) || $tarifa < 0 || $tarifa > 999999.99) {
                throw new \InvalidArgumentException('Las tarifas deben estar entre Bs 0 y Bs 999999,99.');
            }
        }
        if ($minimoAlmuerzosMes < 0 || $minimoAlmuerzosMes > 31) {
            throw new \InvalidArgumentException('El mínimo mensual debe estar entre 0 y 31 almuerzos.');
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horaLimite)) {
            throw new \InvalidArgumentException('La hora límite debe tener un formato válido de 24 horas.');
        }
    }

    public function aArray(): array
    {
        return [
            'tarifa_pensionado' => round($this->tarifaPensionado, 2),
            'tarifa_esporadico' => round($this->tarifaEsporadico, 2),
            'minimo_almuerzos_mes' => $this->minimoAlmuerzosMes,
            'hora_limite' => $this->horaLimite,
        ];
    }
}
