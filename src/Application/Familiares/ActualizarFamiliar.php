<?php
declare(strict_types=1);

namespace Restaurante\Application\Familiares;

use Restaurante\Application\Port\AdministracionFamiliarRepository;

final readonly class ActualizarFamiliar
{
    private const PARENTESCOS_PERMITIDOS = ['PADRE', 'MADRE', 'CONYUGE', 'HIJO', 'HIJA'];

    public function __construct(private AdministracionFamiliarRepository $familiares) {}

    public function ejecutar(int $id, array $datos): ResultadoGestionFamiliar
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $ci = strtoupper((string) preg_replace('/\s+/u', '', trim((string) ($datos['ci'] ?? ''))));
        $parentesco = strtoupper(trim((string) ($datos['parentesco'] ?? '')));
        if ($id < 1 || $nombre === '' || strlen($nombre) > 150) {
            return ResultadoGestionFamiliar::rechazo('Revisa el nombre del familiar.');
        }
        if (strlen($ci) < 3 || strlen($ci) > 20) {
            return ResultadoGestionFamiliar::rechazo('Ingresa un CI válido para el familiar.');
        }
        if (!in_array($parentesco, self::PARENTESCOS_PERMITIDOS, true)) {
            return ResultadoGestionFamiliar::rechazo('Solo se permite registrar padres, cónyuge e hijos.');
        }
        if ($this->familiares->ciUsadoPorOtro($ci, $id)) {
            return ResultadoGestionFamiliar::rechazo('El CI ya está registrado en otro familiar.');
        }
        if (!$this->familiares->actualizar($id, ['nombre' => $nombre, 'ci' => $ci, 'parentesco' => $parentesco])) {
            return ResultadoGestionFamiliar::rechazo('No se encontró el familiar seleccionado.');
        }

        return ResultadoGestionFamiliar::exito('Los datos del familiar se actualizaron.');
    }
}
