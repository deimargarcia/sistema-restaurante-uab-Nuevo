# Historias y pruebas

Esta lista indica qué criterios ya tienen una prueba automatizada y cuáles faltan. La iremos actualizando al trasladar cada función.

## Historia 1: Inicio de sesión según el rol

| Criterio | Prueba |
|---|---|
| El acceso reconoce el correo y normaliza mayúsculas y espacios. | `IniciarSesionTest::testCredencialesCorrectasInicianSesion` |
| El acceso también reconoce el CI. | `IniciarSesionTest::testPermiteIniciarSesionConCi` |
| Una contraseña incorrecta se rechaza. | `IniciarSesionTest::testContrasenaIncorrectaSeRechaza` |
| Una cuenta inactiva no inicia sesión. | `IniciarSesionTest::testCuentaInactivaNoPuedeIniciarSesion` |
| Los campos vacíos se rechazan antes de buscar al usuario. | `IniciarSesionTest::testCamposVaciosSeRechazanAntesDeConsultar` |
| El administrador y el empleado llegan a paneles distintos; una credencial rechazada no crea sesión. | `ControladorAccesoTest` |

## Historia 2: Alta y administración de empleados

| Criterio | Prueba |
|---|---|
| Un registro válido guarda los datos normalizados y la contraseña como hash. | `RegistrarEmpleadoTest::testRegistraEmpleadoNormalizandoDatosYGuardandoHash` |
| Los campos requeridos se validan antes de guardar. | `RegistrarEmpleadoTest::testPideLosCamposObligatoriosQueFaltan` |
| Un correo repetido se rechaza. | `RegistrarEmpleadoTest::testRechazaCorreoDuplicado` |
| Un CI repetido se rechaza. | `RegistrarEmpleadoTest::testRechazaCiDuplicado` |
| Un código repetido y un tipo de cuenta inválido se rechazan. | `RegistrarEmpleadoTest::testRechazaCodigoDeEmpleadoDuplicado` y `testRechazaDatosConFormatoOTipoDeCuentaNoPermitido` |
| Solo administración puede enviar el alta y guardar el empleado. | `ControladorEmpleadosTest` |

## Historias pendientes

| Historia | Cobertura actual |
|---|---|
| 3. Vinculación de familiares directos | Pendiente de migrar el caso de uso y sus pruebas. |
| 4. Afiliación mensual del titular | Pendiente de migrar el caso de uso y sus pruebas. |
| 5. Pensión y reservas para familiares | Pendiente de migrar el caso de uso y sus pruebas. |
| 6. Reservas esporádicas y horario límite | Pendiente de migrar el caso de uso y sus pruebas. |
| 7. Cancelaciones e inasistencias | Pendiente de migrar el caso de uso y sus pruebas. |
| 8. Publicación y consulta del menú | Pendiente de migrar el caso de uso y sus pruebas. |
| 9. Reportes y descuento por planilla | Pendiente de migrar el caso de uso y sus pruebas. |
| 10. Configuración y calendario | Pendiente de migrar el caso de uso y sus pruebas. |
