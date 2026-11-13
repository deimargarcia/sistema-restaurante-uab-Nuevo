<?php
declare(strict_types=1);

namespace Restaurante\Application\Port;

interface ReporteRepository
{
    /** @return array{tarifa_pensionado:float,tarifa_esporadico:float,minimo_almuerzos_mes:int,hora_limite:string} */
    public function reglas(): array;
    public function actualizarEstados(string $hoy, string $hora, string $horaLimite): void;
    /** @return list<array<string,mixed>> */
    public function datosDelMes(int $anio, int $mes): array;
    public function guardar(array $fila): void;
    /** @return list<array<string,mixed>> */
    public function listar(int $anio, int $mes): array;
}
