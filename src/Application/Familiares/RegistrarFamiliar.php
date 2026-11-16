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

    public function ejecutar(int $titularId, string $nombre, string $parentesco, string $ci): ResultadoRegistroFamiliar
    {
        $nombre = trim($nombre);
        $parentesco = strtoupper(trim($parentesco));
        $ci = strtoupper((string) preg_replace('/\s+/u', '', trim($ci)));

        if ($titularId < 1) {
            return ResultadoRegistroFamiliar::rechazo('Selecciona un empleado titular válido.');
        }
        if ($nombre === '' || strlen($nombre) > 150) {
            return ResultadoRegistroFamiliar::rechazo('Ingresa el nombre del familiar.');
        }
        if (strlen($ci) < 3 || strlen($ci) > 20) {
            return ResultadoRegistroFamiliar::rechazo('Ingresa un CI válido para el familiar.');
        }
        if (!in_array($parentesco, self::PARENTESCOS_PERMITIDOS, true)) {
            return ResultadoRegistroFamiliar::rechazo('Solo se permite registrar padres, cónyuge e hijos.');
        }
        if (!$this->familiares->titularEmpleadoExiste($titularId)) {
            return ResultadoRegistroFamiliar::rechazo('No se encontró al empleado titular seleccionado.');
        }
        if ($this->familiares->ciExiste($ci)) {
            return ResultadoRegistroFamiliar::rechazo('El CI ya está registrado como familiar.');
        }

        $familiar = new Familiar($titularId, $nombre, $parentesco, $ci);
        try {
            return ResultadoRegistroFamiliar::exito($this->familiares->crear($familiar));
        } catch (FamiliarDuplicado $error) {
            return ResultadoRegistroFamiliar::rechazo($error->getMessage());
        }
    }
}
