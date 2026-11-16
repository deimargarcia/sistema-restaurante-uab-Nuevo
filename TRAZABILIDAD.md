# Trazabilidad entre historias de usuario y pruebas

Este documento relaciona los criterios de aceptación con las pruebas unitarias existentes en la capa de aplicación y, cuando corresponde, con pruebas de controladores. La presencia de una prueba indica cobertura automatizada de ese comportamiento; no reemplaza la revisión funcional completa ni las pruebas de integración con la base de datos y la interfaz.

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
| Administración puede consultar la lista de empleados y distinguir cuentas activas de suspendidas. | Pendiente de prueba automatizada |
| Administración puede actualizar los datos de una cuenta sin reemplazar la contraseña cuando deja ese campo vacío. | Pendiente de prueba automatizada |
| Correo, CI y código siguen siendo únicos al actualizar un empleado. | Pendiente de prueba automatizada |
| Administración puede suspender o reactivar una cuenta; la suspensión conserva reservas y afiliaciones históricas. | Pendiente de prueba automatizada |

La eliminación se implementa como suspensión lógica. No se borra la fila del empleado porque otras tablas conservan relaciones con ella y el historial de reservas debe mantenerse.

## Historia 3: Vinculación de familiares directos

| Criterio | Prueba |
|---|---|
| El familiar queda vinculado al titular que seleccionó administración. | `RegistrarFamiliarTest::testVinculaFamiliarAlTitularSeleccionado` |
| Se aceptan padres, cónyuge e hijos. | `RegistrarFamiliarTest::testAceptaPadresConyugeEHijos` |
| Se rechaza un parentesco fuera del primer grado. | `RegistrarFamiliarTest::testRechazaUnParentescoFueraDelPrimerGrado` |
| El titular seleccionado debe ser un empleado existente. | `RegistrarFamiliarTest::testExigeUnEmpleadoTitularExistente` |
| Solo administración puede consultar titulares y enviar el registro. | `ControladorFamiliaresTest` |
| El registro exige CI, lo normaliza y evita repetirlo. | CI normalizado: `RegistrarFamiliarTest::testVinculaFamiliarAlTitularSeleccionado`; unicidad pendiente de prueba automatizada |
| Administración puede consultar familiares junto con el empleado titular y su estado. | Pendiente de prueba automatizada |
| Administración puede modificar nombre, CI y parentesco manteniendo el titular vinculado. | Pendiente de prueba automatizada |
| Administración puede desactivar o reactivar un familiar y conservar el historial. | Pendiente de prueba automatizada |

La baja se maneja como desactivación lógica para conservar las reservas y afiliaciones relacionadas.

## Historias implementadas con cobertura automatizada

| Historia | Cobertura actual |
|---|---|
| 4. Afiliación mensual del titular | `AfiliarBeneficiarioTest::testAfiliacionDelTitularCreaReservasDelMesFuturo`, `testProgramaSoloLunesAJuevesYOmiteFeriados`, `testNoPermiteAfiliarAlMesActualNiAUnMesPasado` y `testNoDuplicaUnaAfiliacionDelMismoBeneficiario`. |
| 5. Pensión y reservas para familiares | `AfiliarBeneficiarioTest::testTitularPensionadoPuedeAfiliarFamiliar`, `testNoPensionadoNoPuedeAfiliarFamiliares` y `testFamiliarDebeEstarActivoYVinculadoAlTitular`; el controlador administrativo se cubre en `ControladorPensionamientosTest`. |
| 6. Reservas esporádicas y horario límite | `CrearReservaTest` cubre fecha operativa, feriados, hora límite, menú publicado, titular o familiar autorizado, duplicados, modalidad y tarifa. `ControladorReservasTest` comprueba el acceso a la creación de reservas. |
| 7. Cancelaciones e inasistencias | `CancelarReservaTest` cubre titularidad, estado, hora límite y mínimo mensual. `RegistrarEntregaTest` comprueba que solo se marque una reserva pendiente dentro del horario permitido. La actualización automática de estados se ejecuta al consultar o gestionar reservas, pero falta una prueba unitaria que verifique explícitamente la transición a inasistencia cobrada. |
| 8. Menú semanal y cocina | `PublicarMenuTest` cubre fecha operativa, feriados, campos de sopa, plato principal y postre, e imagen válida; también verifica la consulta semanal. `RegistrarEntregaTest` cubre el registro de entrega y sus restricciones. |
| 9. Reportes y descuento por planilla | `GenerarReporteMensualTest::testCalculaMinimoTarifasPenalizacionFamiliarYTotal` cubre mínimo, tarifas, penalización familiar y total. También se comprueba el cierre del periodo y la persistencia del reporte para consulta. |
| 10. Configuración y calendario | `ParametrosSistemaTest` cubre el mínimo mensual, horario límite, registro/restauración de feriados y validaciones. Las tarifas permanecen fijas: Bs 15 para pensionados y Bs 18 para modalidad esporádica; el caso de uso las conserva aunque se envíen otros valores. |

## Alcance del modelo de menú

Actualmente, `Menu` representa un menú diario y guarda `sopa`, `platoPrincipal` y `postre` como textos, además de la fecha, el administrador que lo publicó y una imagen opcional. No existe una entidad `Plato` independiente ni un catálogo reutilizable de platos: publicar el menú consiste en guardar los nombres de esos tres componentes. La prueba `PublicarMenuTest` refleja ese modelo.

Por tanto, el diagrama de clases debe mostrar por ahora `Menu` con esos atributos de texto. Si se decide incorporar `Plato` como entidad, también habrá que cambiar el dominio, los casos de uso y la persistencia; no conviene incluirla únicamente en el diagrama porque este debe coincidir con las clases implementadas.
