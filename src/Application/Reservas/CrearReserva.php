<?php
declare(strict_types=1);

namespace Restaurante\Application\Reservas;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Restaurante\Application\Port\ReservaRepository;
use Restaurante\Domain\Entity\Reserva;

final readonly class CrearReserva
{
    private Closure $ahora;

    public function __construct(private ReservaRepository $reservas, ?Closure $ahora = null)
    {
        $this->ahora = $ahora ?? static fn (): DateTimeImmutable => new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
    }

    public function ejecutar(int $titularId, ?int $familiarId, string $fecha): ResultadoReserva
    {
        $fechaReserva = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaReserva || $fechaReserva->format('Y-m-d') !== $fecha) {
            return ResultadoReserva::rechazo('Selecciona una fecha válida.');
        }

        $ahora = ($this->ahora)();
        $hoy = $ahora->format('Y-m-d');
        if ($fecha < $hoy) {
            return ResultadoReserva::rechazo('No puedes reservar para una fecha pasada.');
        }
        if ((int) $fechaReserva->format('N') > 4 || $this->reservas->esFeriado($fecha)) {
            return ResultadoReserva::rechazo('Solo se puede reservar de lunes a jueves y en días no feriados.');
        }
        $reglas = $this->reservas->reglas();
        if ($fecha === $hoy && $ahora->format('H:i:s') >= $reglas['hora_limite']) {
            return ResultadoReserva::rechazo('Ya pasó el horario límite para reservar el almuerzo de hoy.');
        }
        if (!$this->reservas->menuPublicado($fecha)) {
            return ResultadoReserva::rechazo('No hay un menú publicado para ese día.');
        }

        $tipoCuenta = $this->reservas->tipoCuentaTitular($titularId);
        if (!in_array($tipoCuenta, ['PENSIONADO', 'NO_PENSIONADO'], true)) {
            return ResultadoReserva::rechazo('La cuenta del empleado no está habilitada para reservar.');
        }
        if ($familiarId !== null && ($tipoCuenta !== 'PENSIONADO' || !$this->reservas->familiarActivoPertenece($titularId, $familiarId))) {
            return ResultadoReserva::rechazo('Solo un titular pensionado puede reservar para un familiar activo vinculado.');
        }
        if ($this->reservas->reservaActivaExiste($titularId, $familiarId, $fecha)) {
            return ResultadoReserva::rechazo('Ese beneficiario ya tiene una reserva para esa fecha.');
        }

        $modalidad = $this->reservas->existeAfiliacion(
            $titularId,
            $familiarId,
            (int) $fechaReserva->format('Y'),
            (int) $fechaReserva->format('m'),
        ) ? 'PENSIONADO' : 'ESPORADICO';
        $reserva = new Reserva($titularId, $familiarId, $fecha, $modalidad);

        try {
            $id = $this->reservas->crear($reserva);
        } catch (\PDOException $error) {
            if ((string) $error->getCode() === '23000' && (int) ($error->errorInfo[1] ?? 0) === 1062) {
                return ResultadoReserva::rechazo('Ese beneficiario ya tiene una reserva para esa fecha.');
            }
            throw $error;
        }

        $tarifa = $modalidad === 'PENSIONADO' ? $reglas['tarifa_pensionado'] : $reglas['tarifa_esporadico'];
        return ResultadoReserva::exito($id, $tarifa, 'Reserva realizada correctamente.');
    }
}
