<?php
declare(strict_types=1);

namespace Restaurante\Application\Empleados;

use Restaurante\Application\Port\EmpleadoAdministracionRepository;

final readonly class ActualizarEmpleado
{
    public function __construct(private EmpleadoAdministracionRepository $empleados)
    {
    }

    public function ejecutar(int $id, array $datos): ResultadoGestionEmpleado
    {
        if ($id < 1) {
            return ResultadoGestionEmpleado::rechazo('Selecciona un empleado válido.');
        }

        $datos = [
            'nombre' => trim((string) ($datos['nombre'] ?? '')),
            'apellido' => trim((string) ($datos['apellido'] ?? '')),
            'correo' => strtolower(trim((string) ($datos['correo'] ?? ''))),
            'celular' => trim((string) ($datos['celular'] ?? '')),
            'ci' => strtoupper((string) preg_replace('/\s+/u', '', trim((string) ($datos['ci'] ?? '')))),
            'departamento' => trim((string) ($datos['departamento'] ?? '')),
            'codigo_empleado' => strtoupper(trim((string) ($datos['codigo_empleado'] ?? ''))),
            'tipo_cuenta' => strtoupper(trim((string) ($datos['tipo_cuenta'] ?? ''))),
        ];
        $contrasena = (string) ($datos['contrasena'] ?? '');

        foreach (['nombre', 'apellido', 'correo', 'celular', 'ci', 'departamento', 'codigo_empleado', 'tipo_cuenta'] as $campo) {
            if ($datos[$campo] === '') {
                return ResultadoGestionEmpleado::rechazo('Completa todos los campos obligatorios.');
            }
        }
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            return ResultadoGestionEmpleado::rechazo('Ingresa un correo electrónico válido.');
        }
        if (strlen($datos['nombre']) > 150 || strlen($datos['apellido']) > 100
            || strlen($datos['nombre'] . ' ' . $datos['apellido']) > 150
            || strlen($datos['correo']) > 150 || strlen($datos['celular']) > 20
            || strlen($datos['ci']) < 3 || strlen($datos['ci']) > 20
            || strlen($datos['departamento']) > 100 || strlen($datos['codigo_empleado']) > 50) {
            return ResultadoGestionEmpleado::rechazo('Revisa la longitud de los datos ingresados.');
        }
        if (!in_array($datos['tipo_cuenta'], ['PENSIONADO', 'NO_PENSIONADO'], true)) {
            return ResultadoGestionEmpleado::rechazo('El tipo de cuenta no es válido.');
        }
        if ($contrasena !== '' && strlen($contrasena) < 6) {
            return ResultadoGestionEmpleado::rechazo('La nueva contraseña debe tener al menos 6 caracteres.');
        }
        if ($this->empleados->correoUsadoPorOtro($datos['correo'], $id)) {
            return ResultadoGestionEmpleado::rechazo('El correo ya está registrado en otra cuenta.');
        }
        if ($this->empleados->ciUsadoPorOtro($datos['ci'], $id)) {
            return ResultadoGestionEmpleado::rechazo('El CI ya está registrado en otra cuenta.');
        }
        if ($this->empleados->codigoUsadoPorOtro($datos['codigo_empleado'], $id)) {
            return ResultadoGestionEmpleado::rechazo('El código ya está registrado en otra cuenta.');
        }
        if ($contrasena !== '') {
            $datos['contrasena'] = password_hash($contrasena, PASSWORD_DEFAULT);
        }

        return $this->empleados->actualizar($id, $datos)
            ? ResultadoGestionEmpleado::exito('Los datos del empleado se actualizaron.')
            : ResultadoGestionEmpleado::rechazo('No se encontró el empleado o no hubo cambios.');
    }
}
