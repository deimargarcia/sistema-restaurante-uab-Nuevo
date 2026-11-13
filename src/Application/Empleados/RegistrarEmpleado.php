<?php
declare(strict_types=1);

namespace Restaurante\Application\Empleados;

use Restaurante\Application\Port\EmpleadoRepository;
use Restaurante\Domain\Entity\Empleado;

final readonly class RegistrarEmpleado
{
    public function __construct(private EmpleadoRepository $empleados)
    {
    }

    public function ejecutar(array $datos): ResultadoRegistroEmpleado
    {
        $datos = $this->normalizar($datos);
        $error = $this->validar($datos);
        if ($error !== null) {
            return ResultadoRegistroEmpleado::rechazo($error);
        }

        if ($this->empleados->correoExiste($datos['correo'])) {
            return ResultadoRegistroEmpleado::rechazo('El correo ya está registrado.');
        }
        if ($this->empleados->ciExiste($datos['ci'])) {
            return ResultadoRegistroEmpleado::rechazo('El CI ya está registrado.');
        }
        if ($this->empleados->codigoExiste($datos['codigo_empleado'])) {
            return ResultadoRegistroEmpleado::rechazo('El código de empleado ya está en uso.');
        }

        $empleado = new Empleado(
            $datos['nombre'],
            $datos['apellido'],
            $datos['correo'],
            password_hash($datos['contrasena'], PASSWORD_DEFAULT),
            $datos['celular'],
            $datos['ci'],
            $datos['departamento'],
            $datos['codigo_empleado'],
            $datos['tipo_cuenta'],
        );

        return ResultadoRegistroEmpleado::exito($this->empleados->crear($empleado));
    }

    private function normalizar(array $datos): array
    {
        return [
            'nombre' => trim((string) ($datos['nombre'] ?? '')),
            'apellido' => trim((string) ($datos['apellido'] ?? '')),
            'correo' => strtolower(trim((string) ($datos['correo'] ?? ''))),
            'contrasena' => (string) ($datos['contrasena'] ?? ''),
            'celular' => trim((string) ($datos['celular'] ?? '')),
            'ci' => strtoupper((string) preg_replace('/\s+/u', '', trim((string) ($datos['ci'] ?? '')))),
            'departamento' => trim((string) ($datos['departamento'] ?? '')),
            'codigo_empleado' => strtoupper(trim((string) ($datos['codigo_empleado'] ?? ''))),
            'tipo_cuenta' => strtoupper(trim((string) ($datos['tipo_cuenta'] ?? ''))),
        ];
    }

    private function validar(array $datos): ?string
    {
        foreach ([
            'nombre' => 'nombre',
            'apellido' => 'apellido',
            'correo' => 'correo',
            'contrasena' => 'contraseña',
            'celular' => 'teléfono',
            'ci' => 'CI',
            'departamento' => 'departamento',
            'codigo_empleado' => 'código de empleado',
            'tipo_cuenta' => 'tipo de cuenta',
        ] as $campo => $etiqueta) {
            if ($datos[$campo] === '') {
                return 'El campo ' . $etiqueta . ' es obligatorio.';
            }
        }

        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) || strlen($datos['correo']) > 150) {
            return 'Ingresa un correo electrónico válido.';
        }
        if (strlen($datos['contrasena']) < 6) {
            return 'La contraseña debe tener al menos 6 caracteres.';
        }
        if (strlen($datos['nombre']) > 150 || strlen($datos['apellido']) > 100
            || strlen($datos['nombre'] . ' ' . $datos['apellido']) > 150
            || strlen($datos['departamento']) > 100) {
            return 'Revisa la longitud del nombre, apellido o departamento.';
        }
        if (strlen($datos['ci']) < 3 || strlen($datos['ci']) > 20) {
            return 'El CI debe tener entre 3 y 20 caracteres.';
        }
        if (strlen($datos['celular']) > 20 || strlen($datos['codigo_empleado']) > 50) {
            return 'Revisa la longitud del teléfono o código de empleado.';
        }
        if (!in_array($datos['tipo_cuenta'], ['PENSIONADO', 'NO_PENSIONADO'], true)) {
            return 'El tipo de cuenta no es válido.';
        }

        return null;
    }
}
