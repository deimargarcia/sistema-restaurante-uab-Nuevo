<?php
declare(strict_types=1);

namespace Restaurante\Application\Reservas;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Restaurante\Application\Port\ReservaRepository;

final readonly class ListarReservasDia
{
    private Closure $ahora;

    public function __construct(private ReservaRepository $reservas, ?Closure $ahora = null)
    {
        $this->ahora = $ahora ?? static fn (): DateTimeImmutable => new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
    }

    public function ejecutar(string $fecha): array
    {
        $fechaObjeto = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaObjeto || $fechaObjeto->format('Y-m-d') !== $fecha) {
            return [];
        }
        $ahora = ($this->ahora)();
        $reglas = $this->reservas->reglas();
        $this->reservas->actualizarEstadosAutomaticos($ahora->format('Y-m-d'), $ahora->format('H:i:s'), $reglas['hora_limite']);
        return $this->reservas->listarPorFecha($fecha);
    }
}
