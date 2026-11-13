<?php
declare(strict_types=1);

namespace Restaurante\Application\Pensionamientos;

use Closure;
use DateTimeImmutable;
use Restaurante\Application\Port\PensionamientoRepository;
use Restaurante\Domain\Entity\Pensionamiento;

final readonly class AfiliarBeneficiario
{
    private Closure $ahora;

    public function __construct(
        private PensionamientoRepository $pensionamientos,
        ?Closure $ahora = null,
    ) {
        $this->ahora = $ahora ?? static fn (): DateTimeImmutable => new DateTimeImmutable('now');
    }

    public function ejecutar(
        int $titularId,
        ?int $familiarId,
        int $anio,
        int $mes,
        int $registradoPorId,
    ): ResultadoAfiliacion {
        if ($registradoPorId < 1 || !checkdate($mes, 1, $anio)) {
            return ResultadoAfiliacion::rechazo('Selecciona un mes válido para la afiliación.');
        }

        $inicioMes = new DateTimeImmutable(sprintf('%04d-%02d-01', $anio, $mes));
        $mesActual = ($this->ahora)()->modify('first day of this month');
        if ($inicioMes <= $mesActual) {
            return ResultadoAfiliacion::rechazo('No se puede afiliar a mitad de mes; el periodo ya comenzó.');
        }

        $tipoCuenta = $this->pensionamientos->tipoCuentaTitular($titularId);
        if (!in_array($tipoCuenta, ['PENSIONADO', 'NO_PENSIONADO'], true)) {
            return ResultadoAfiliacion::rechazo('El empleado no está activo o no puede afiliarse a la pensión.');
        }

        if ($familiarId !== null) {
            if ($tipoCuenta !== 'PENSIONADO') {
                return ResultadoAfiliacion::rechazo('Solo un titular pensionado puede afiliar familiares.');
            }
            if (!$this->pensionamientos->familiarActivoPertenece($titularId, $familiarId)) {
                return ResultadoAfiliacion::rechazo('El familiar debe estar activo y vinculado al titular.');
            }
        }

        $pensionamiento = new Pensionamiento($titularId, $anio, $mes, $familiarId);
        $feriados = array_fill_keys($this->pensionamientos->feriadosDelMes($anio, $mes), true);
        $fechas = $this->fechasOperativas($anio, $mes, $feriados);

        try {
            $reservas = $this->pensionamientos->registrarConReservas($pensionamiento, $registradoPorId, $fechas);
        } catch (AfiliacionDuplicada $error) {
            return ResultadoAfiliacion::rechazo($error->getMessage());
        }

        return ResultadoAfiliacion::exito($reservas);
    }

    private function fechasOperativas(int $anio, int $mes, array $feriados): array
    {
        $inicio = new DateTimeImmutable(sprintf('%04d-%02d-01', $anio, $mes));
        $fin = $inicio->modify('last day of this month');
        $fechas = [];

        for ($dia = $inicio; $dia <= $fin; $dia = $dia->modify('+1 day')) {
            $fecha = $dia->format('Y-m-d');
            if ((int) $dia->format('N') <= 4 && !isset($feriados[$fecha])) {
                $fechas[] = $fecha;
            }
        }

        return $fechas;
    }
}
