<?php
declare(strict_types=1);

namespace Restaurante\Application\Reservas;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Restaurante\Application\Port\ReservaRepository;

final readonly class CancelarReserva
{
    private Closure $ahora;

    public function __construct(private ReservaRepository $reservas, ?Closure $ahora = null)
    {
        $this->ahora = $ahora ?? static fn (): DateTimeImmutable => new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
    }

    public function ejecutar(int $reservaId, int $titularId): ResultadoReserva
    {
        if ($reservaId < 1 || $titularId < 1) {
            return ResultadoReserva::rechazo('La reserva seleccionada no es válida.');
        }

        $ahora = ($this->ahora)();
        $reglas = $this->reservas->reglas();
        $this->reservas->actualizarEstadosAutomaticos($ahora->format('Y-m-d'), $ahora->format('H:i:s'), $reglas['hora_limite']);
        $reserva = $this->reservas->buscarParaCancelar($reservaId, $titularId);
        if ($reserva === null) {
            return ResultadoReserva::rechazo('La reserva no existe o no pertenece a tu cuenta.');
        }
        if ($reserva['estado'] !== 'RESERVADO') {
            return ResultadoReserva::rechazo('Esta reserva ya no se puede cancelar.');
        }

        $hoy = $ahora->format('Y-m-d');
        if ($reserva['fecha_reserva'] < $hoy) {
            return ResultadoReserva::rechazo('No puedes cancelar reservas de fechas pasadas.');
        }
        if ($reserva['fecha_reserva'] === $hoy && $ahora->format('H:i:s') >= $reglas['hora_limite']) {
            return ResultadoReserva::rechazo('La reserva de hoy solo puede cancelarse antes de las ' . substr($reglas['hora_limite'], 0, 5) . '.');
        }

        if ($reserva['modalidad'] === 'PENSIONADO') {
            $fecha = new DateTimeImmutable($reserva['fecha_reserva']);
            $familiarId = $reserva['tipo_beneficiario'] === 'FAMILIAR' ? (int) $reserva['familiar_id'] : null;
            $cobrables = $this->reservas->contarCobrablesMes(
                $titularId,
                $familiarId,
                (int) $fecha->format('Y'),
                (int) $fecha->format('m'),
            );
            if ($cobrables <= $reglas['minimo_almuerzos_mes']) {
                return ResultadoReserva::rechazo(
                    'No puedes cancelar porque quedarías por debajo del mínimo de ' . $reglas['minimo_almuerzos_mes'] . ' almuerzos del mes.'
                );
            }
        }

        if (!$this->reservas->cancelar($reservaId)) {
            return ResultadoReserva::rechazo('La reserva cambió de estado y ya no se puede cancelar.');
        }
        return ResultadoReserva::exito($reservaId, 0.0, 'Reserva cancelada correctamente.');
    }
}
