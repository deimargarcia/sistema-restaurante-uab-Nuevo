<?php
declare(strict_types=1);

namespace Restaurante\Adapters\Http;

use Restaurante\Application\Reservas\CancelarReserva;
use Restaurante\Application\Reservas\CrearReserva;
use Restaurante\Application\Reservas\ListarReservasTitular;
use Restaurante\Application\Reservas\ListarReservasDia;
use Restaurante\Application\Reservas\RegistrarEntrega;

final readonly class ControladorReservas
{
    public function __construct(
        private CrearReserva $crearReserva,
        private CancelarReserva $cancelarReserva,
        private ListarReservasTitular $listarReservas,
        private ListarReservasDia $listarDia,
        private RegistrarEntrega $registrarEntrega,
    ) {
    }

    public function listar(int $titularId): array
    {
        if (!$this->empleadoPuedeReservar()) {
            return ['exito' => false, 'mensaje' => 'Solo un empleado puede consultar sus reservas.'];
        }
        return ['exito' => true, 'reservas' => $this->listarReservas->ejecutar($titularId)];
    }

    public function crear(int $titularId, ?int $familiarId, string $fecha): array
    {
        if (!$this->empleadoPuedeReservar()) {
            return ['exito' => false, 'mensaje' => 'Solo un empleado puede realizar reservas.'];
        }
        $resultado = $this->crearReserva->ejecutar($titularId, $familiarId, $fecha);
        return [
            'exito' => $resultado->exitoso,
            'mensaje' => $resultado->mensaje,
            'reserva_id' => $resultado->reservaId,
            'tarifa' => $resultado->tarifa,
        ];
    }

    public function cancelar(int $titularId, int $reservaId): array
    {
        if (!$this->empleadoPuedeReservar()) {
            return ['exito' => false, 'mensaje' => 'Solo un empleado puede cancelar reservas.'];
        }
        $resultado = $this->cancelarReserva->ejecutar($reservaId, $titularId);
        return ['exito' => $resultado->exitoso, 'mensaje' => $resultado->mensaje];
    }

    public function listarDia(string $fecha): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
            return ['exito' => false, 'mensaje' => 'Solo administración puede consultar el consolidado diario.'];
        }
        return ['exito' => true, 'reservas' => $this->listarDia->ejecutar($fecha)];
    }

    public function registrarEntrega(int $reservaId): array
    {
        if (($_SESSION['tipo_cuenta'] ?? '') !== 'ADMIN') {
            return ['exito' => false, 'mensaje' => 'Solo administración puede registrar entregas.'];
        }
        $resultado = $this->registrarEntrega->ejecutar($reservaId);
        return ['exito' => $resultado->exitoso, 'mensaje' => $resultado->mensaje];
    }

    private function empleadoPuedeReservar(): bool
    {
        return in_array($_SESSION['tipo_cuenta'] ?? '', ['PENSIONADO', 'NO_PENSIONADO'], true);
    }
}
