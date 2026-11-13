<?php
declare(strict_types=1);

namespace Restaurante\Application\Reservas;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Restaurante\Application\Port\ReservaRepository;

final readonly class RegistrarEntrega
{
    private Closure $ahora;

    public function __construct(private ReservaRepository $reservas, ?Closure $ahora = null)
    {
        $this->ahora = $ahora ?? static fn (): DateTimeImmutable => new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
    }

    public function ejecutar(int $reservaId): ResultadoReserva
    {
        $ahora = ($this->ahora)();
        $hoy = $ahora->format('Y-m-d');
        $reglas = $this->reservas->reglas();
        if ($ahora->format('H:i:s') < $reglas['hora_limite']) {
            return ResultadoReserva::rechazo('El registro de entregas se habilita después de las ' . substr($reglas['hora_limite'], 0, 5) . '.');
        }
        $this->reservas->actualizarEstadosAutomaticos($hoy, $ahora->format('H:i:s'), $reglas['hora_limite']);
        if (!$this->reservas->marcarEntregada($reservaId, $hoy)) {
            return ResultadoReserva::rechazo('La reserva no está pendiente de entrega para hoy.');
        }
        return ResultadoReserva::exito($reservaId, 0.0, 'Entrega registrada.');
    }
}
