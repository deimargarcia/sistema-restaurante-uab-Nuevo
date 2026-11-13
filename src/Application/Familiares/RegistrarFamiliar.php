<?php
declare(strict_types=1);

namespace Restaurante\Application\Familiares;

use Restaurante\Application\Port\FamiliarRepository;
use Restaurante\Domain\Entity\Familiar;

final readonly class RegistrarFamiliar
{
    private const PARENTESCOS_PERMITIDOS = ['PADRE', 'MADRE', 'CONYUGE', 'HIJO', 'HIJA'];

    public function __construct(private FamiliarRepository $familiares)
    {
    }

    public function ejecutar(int $titularId, string $nombre, string $parentesco): ResultadoRegistroFamiliar
    {
        $nombre = trim($nombre);
        $parentesco = strtoupper(trim($parentesco));

        if ($titularId < 1) {
            return ResultadoRegistroFamiliar::rechazo('Selecciona un empleado titular válido.');
        }
        if ($nombre === '' || strlen($nombre) > 150) {
            return ResultadoRegistroFamiliar::rechazo('Ingresa el nombre del familiar.');
        }
        if (!in_array($parentesco, self::PARENTESCOS_PERMITIDOS, true)) {
            return ResultadoRegistroFamiliar::rechazo('Solo se permite registrar padres, cónyuge e hijos.');
        }
        if (!$this->familiares->titularEmpleadoExiste($titularId)) {
            return ResultadoRegistroFamiliar::rechazo('No se encontró al empleado titular seleccionado.');
        }

        $familiar = new Familiar($titularId, $nombre, $parentesco);
        return ResultadoRegistroFamiliar::exito($this->familiares->crear($familiar));
    }
}
