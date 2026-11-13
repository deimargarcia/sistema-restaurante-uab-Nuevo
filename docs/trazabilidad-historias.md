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

## Historia 3: Vinculación de familiares directos

| Criterio | Prueba |
|---|---|
| El familiar queda vinculado al titular que seleccionó administración. | `RegistrarFamiliarTest::testVinculaFamiliarAlTitularSeleccionado` |
| Se aceptan padres, cónyuge e hijos. | `RegistrarFamiliarTest::testAceptaPadresConyugeEHijos` |
| Se rechaza un parentesco fuera del primer grado. | `RegistrarFamiliarTest::testRechazaUnParentescoFueraDelPrimerGrado` |
| El titular seleccionado debe ser un empleado existente. | `RegistrarFamiliarTest::testExigeUnEmpleadoTitularExistente` |
| Solo administración puede consultar titulares y enviar el registro. | `ControladorFamiliaresTest` |

## Historias en desarrollo

| Historia | Cobertura actual |
|---|---|
| 4. Afiliación mensual del titular | `AfiliarBeneficiarioTest::testAfiliacionDelTitularCreaReservasDelMesFuturo`, `testProgramaSoloLunesAJuevesYOmiteFeriados`, `testNoPermiteAfiliarAlMesActualNiAUnMesPasado` y `testNoDuplicaUnaAfiliacionDelMismoBeneficiario`. |
| 5. Pensión y reservas para familiares | `AfiliarBeneficiarioTest::testTitularPensionadoPuedeAfiliarFamiliar`, `testNoPensionadoNoPuedeAfiliarFamiliares` y `testFamiliarDebeEstarActivoYVinculadoAlTitular`; el administrador también puede asistir con `ControladorPensionamientosTest`. |
| 6. Reservas esporádicas y horario límite | Pendiente de migrar el caso de uso y sus pruebas. |
| 7. Cancelaciones e inasistencias | Pendiente de migrar el caso de uso y sus pruebas. |
| 8. Publicación y consulta del menú | Pendiente de migrar el caso de uso y sus pruebas. |
| 9. Reportes y descuento por planilla | Pendiente de migrar el caso de uso y sus pruebas. |
| 10. Configuración y calendario | Pendiente de migrar el caso de uso y sus pruebas. |
