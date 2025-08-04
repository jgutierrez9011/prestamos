<?php
require_once '../usuarios/reg.php';
require_once '../../menu_builder.php';
require_once '../../acceso_helper.php'; // nuevo archivo

$usuario = $_SESSION['user'] ?? null;
$archivoActual = basename($_SERVER['PHP_SELF']);

if (!$usuario || !validarAcceso($usuario, $archivoActual, $base_de_datos)) {
    header("Location: $ruta");
    exit;
}

$perfilUsuario = $_SESSION['perfilusuario'] ?? 'Usuario';
$codigoCartera = $_SESSION['carterausuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Blank Page</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="../../plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  <?php //require_once '../../menu.php';
if (!empty($_SESSION["user"])) {
  $menuBuilder = new MenuBuilder($base_de_datos, $_SESSION["user"]);
  echo $menuBuilder->buildMenu();
}
?>
  <!-- TERMINA EL MENU -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Solicitud de crédito</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Créditos</a></li>
              <li class="breadcrumb-item active">Solicitud de crédito</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row justify-content-center">
        <div class="col-md-10">
          <!-- Tabs de navegación -->
          <ul class="nav nav-pills mb-4 justify-content-center" id="step-indicators">
            <li class="nav-item"><a class="nav-link active" data-step="1" href="#">1. Información del Cliente</a></li>
            <li class="nav-item"><a class="nav-link" data-step="2" href="#">2. Información del Crédito</a></li>
            <li class="nav-item"><a class="nav-link" data-step="3" href="#">3. Información Financiera</a></li>
          </ul>

          <!-- Formulario 1 -->
          <form id="form1" class="form-step">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Información del Cliente</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">

              <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="cedula">Cédula</label>
                                <div class="input-group">
            <input type="text" class="form-control form-control-sm" id="cedula" name="cedula" required>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="searchButton">
                    Buscar
                </button>
            </div>
        </div>
                               <input type="hidden" class="form-control form-control-sm" id="idcliente" name="idcliente" readonly required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" readonly required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control form-control-sm" id="telefono" name="telefono" readonly required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="estado_civil">Estado Civil</label>
                                <select class="form-control form-control-sm" id="estado_civil" name="estado_civil" disabled required>
                                    <option value="">Seleccione...</option>
                                    <option value="soltero">Soltero</option>
                                    <option value="casado">Casado</option>
                                    <option value="union de hecho">Unión de Hecho</option>
                                </select>
                            </div>
                            <!--<div class="form-group col-md-4">
                                <label for="tipo">Tipo</label>
                                <select class="form-control form-control-sm" id="tipo" name="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Cliente nuevo">Cliente Nuevo</option>
                                    <option value="recurrente">Recurrente</option>
                                </select>
                            </div> -->
                            <div class="form-group col-md-4">
                                <label for="tipo_vivienda">Tipo de Vivienda</label>
                                <select class="form-control form-control-sm" id="tipo_vivienda" name="tipo_vivienda" disabled required>
                                    <option value="">Seleccione...</option>
                                    <option value="propio">Propio</option>
                                    <option value="renta">Renta</option>
                                    <option value="albergue">Albergue</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="anos_habitar">Años de Habitar</label>
                                <input type="number" class="form-control form-control-sm" id="anos_habitar" name="anos_habitar" min="0" readonly required>
                            </div>
                            
                        </div>
                        <div class="form-row">
                        <div class="form-group col-md-12">
                                <label for="direccion_domicilio">Dirección del Domicilio</label>
                                <input type="text" class="form-control form-control-sm" id="direccion_domicilio" name="direccion_domicilio" readonly required>
                            </div>
                        </div>
                        <div class="form-row">
                        <div class="form-group col-md-4">
                                <label for="actividad_economica">Actividad Económica</label>
                                <input type="text" class="form-control form-control-sm" id="actividad_economica" name="actividad_economica" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="rubro">Rubro</label>
                                <select class="form-control form-control-sm" id="rubro" name="rubro" required>
                                    <option value="">Seleccione...</option>
                                    <option value="comercio">Comercio</option>
                                    <option value="servicio">Servicio</option>
                                    <option value="produccion">Producción</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="tipo_local">Tipo de Local</label>
                                <select class="form-control form-control-sm" id="tipo_local" name="tipo_local" required>
                                    <option value="">Seleccione...</option>
                                    <option value="propio">Propio</option>
                                    <option value="renta">Renta</option>
                                    <option value="albergue">Albergue</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="tiempo_operar">Tiempo de Operar (mínimo 6 meses)</label>
                                <input type="number" class="form-control form-control-sm" id="tiempo_operar" name="tiempo_operar" min="6" required>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="direccion_negocio">Dirección del Negocio</label>
                                <input type="text" class="form-control form-control-sm" id="direccion_negocio" name="direccion_negocio" required>
                            </div>
                        </div>
                        
                        <div class="container mt-5">
                            <div class="d-flex justify-content-start">
                              <a href="../../pages/clientes/nuevo_cliente.php" class="btn btn-primary mr-2" role="button">
                                Agregar cliente
                              </a>
                              <button type="button" id="next-btn-1" class="btn btn-primary" disabled>
                                Siguiente
                              </button>
                            </div>
                      </div>
                
              </div>
              <div class="card-footer">
                Footer
              </div>
            </div>
          </form>

          <!-- Formulario 2 -->
          <form id="form2" class="form-step d-none">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Información del Crédito</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">

              <div class="form-group">
                                <label for="montoSolicitado">Monto Solicitado:</label>
                                <input type="hidden" id="cod_cartera" name="cod_cartera" class="form-control" value="<?php echo $_SESSION["carterausuario"] ?>">
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="monto_solicitado" name="monto_solicitado" required>
                            </div>
                            <div class="form-group">
                                <label for="plazoSolicitado">Plazo Solicitado (meses):</label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="plazo_solicitado" name="plazo_solicitado" required>
                            </div>
                            <div class="form-group">
                                <label for="tasa">Tasa (%):</label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="tasa" name="tasa" required>
                            </div>
                           <!-- <div class="form-group">
                                <label for="garantia">Garantía:</label>
                                <input type="text" class="form-control form-control-sm" id="garantia" name="garantia" required>
                            </div> -->

                <button type="button" id="back-btn-1" class="btn btn-secondary">Atrás</button>
                <button type="button" id="next-btn-2" class="btn btn-primary" disabled>Siguiente</button>
              </div>
              <div class="card-footer">
                Footer
              </div>
            </div>
          </form>

          <!-- Formulario 3 -->
          <form id="form3" class="form-step d-none">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Información Financiera</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">

    <!-- Fila 1: Tipo de Promedio, Ventas Promedio y Promedio de Venta -->
    <fieldset class="border p-2 mb-3">
      <legend class="w-auto">Ventas Promedio</legend>
      <div class="row">
        <!-- Tipo de Promedio -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="tipoPromedio">Tipo promedio:</label>
                                <select class="form-control form-control-sm" id="tipo_promedio" name="tipo_promedio" required>
                                    <option value="Diario">Diario</option>
                                    <option value="Semanal">Semanal</option>
                                </select>
          </div>
        </div>
        <!-- Venta Promedio Diaria Buena -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="venta_promedio_bueno">Buena:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="venta_promedio_bueno" name="venta_promedio_bueno" required>
          </div>
        </div>
        <!-- Venta Promedio Diaria Mediana -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="venta_promedio_mediano">Mediana:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="venta_promedio_mediano" name="venta_promedio_mediano" required>
          </div>
        </div>
        <!-- Venta Promedio Diaria Baja -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="venta_promedio_bajo">Baja:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="venta_promedio_bajo" name="venta_promedio_bajo" required>
          </div>
        </div>
        <!-- Promedio de Venta -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="promedio_venta">Venta prom.:</label>
            <div class="input-group">
                <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="promedio_venta" name="promedio_venta" readonly required>
              </div>
          </div>
        </div>

      </div> <!-- Fin de la fila 1 -->
    </fieldset>

    <!-- Fila 2: Ingresos y Gastos -->
    <div class="row mt-4"> <!-- mt-4 para agregar un margen superior -->
      <!-- Columna 1: Ingresos -->
      <div class="col-md-6">
        <fieldset class="border p-2">
          <legend class="w-auto">Ingresos</legend>
          <div class="form-group">
            <label for="ventas_mensuales">Ingresos Ventas Mensuales:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="ventas_mensuales" name="ventas_mensuales" required>
          </div>
          <div class="form-group">
            <label for="otros_ingresos_negocio">Otros Ingresos del Negocio:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="otros_ingresos_negocio" name="otros_ingresos_negocio" required>
          </div>
          <div class="form-group">
            <label for="aportes_familiares">Aportes Familiares:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="aportes_familiares" name="aportes_familiares" required>
          </div>
          <div class="form-group">
            <label for="otros_ingresos">Otros Ingresos:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="otros_ingresos" name="otros_ingresos" required>
          </div>
          <div class="form-group">
            <label for="total_ingresos">Total Ingresos:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="total_ingresos" name="total_ingresos" readonly required>
          </div>
        </fieldset>
      </div>

      <!-- Columna 2: Gastos -->
      <div class="col-md-6">
        <fieldset class="border p-2">
          <legend class="w-auto">Gastos</legend>

                <!-- Campos para Producción -->
          <div id="camposProduccion" style="display: none;">
              <div class="form-group">
                <label for="costo_unitario">Costo unitario:</label>
                <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="costo_unitario" name="costo_unitario" placeholder="Costo Unitario">
              </div>
              <div class="form-group">
                <label for="precio_venta">Precio de venta:</label>
                <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="precio_venta" name="precio_venta" placeholder="Precio de Venta">
              </div>
              <div class="form-group">
                <label for="unidad_producida">Unidades producidas:</label>
                <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="unidad_producida" name="unidad_producida" placeholder="Unidades Producidas">
              </div>
          </div>

          <div class="form-group">
            <label for="gasto_costo_venta">Costos de Venta:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="gasto_costo_venta" name="gasto_costo_venta" required>
          </div>
          <div class="form-group">
            <label for="gastos_negocio">Gastos del Negocio:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="gastos_negocio" name="gastos_negocio" required>
          </div>
          <div class="form-group">
            <label for="cuotas_credito">Cuotas de Crédito:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="cuotas_credito" name="cuotas_credito" required>
          </div>
          <div class="form-group">
            <label for="gastos_familiares">Gastos Familiares:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="gastos_familiares" name="gastos_familiares" required>
          </div>
          <div class="form-group">
            <label for="total_gastos">Total gastos:</label>
            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="total_gastos" name="total_gastos" readonly required>
          </div>
        </fieldset>
      </div>
    </div> <!-- Fin de la fila 2 -->

    <!-- Fila 3: Utilidad Final -->
    <div class="row mt-4"> <!-- mt-4 para agregar un margen superior -->
      <div class="col-md-12">
        <div class="form-group">
          <label for="utilidad_final">Utilidad Final:</label>
          <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="utilidad_final" name="utilidad_final" required>
        </div>
      </div>
    </div> <!-- Fin de la fila 3 -->
                <button type="button" id="back-btn-2" class="btn btn-secondary">Atrás</button>
                <button type="button" class="btn btn-info" id="btnCalVentaPromedio"><i class="fas fa-calculator"></i> Calcular</button>
                <button type="submit" class="btn btn-success" id="btn_enviar_solicitud" disabled>Enviar</button>

              </div>
              <div class="card-footer">
                Footer
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- FOOTER -->
  <?php require_once '../../footer.php'; ?>
  <!-- FOOTER -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>
<script>
  $(function () {

    let valorSeleccionado = '';
    const forms = document.querySelectorAll('.form-step');
    const indicators = document.querySelectorAll('#step-indicators .nav-link');

    const buttons = {
      next1: document.getElementById('next-btn-1'),
      next2: document.getElementById('next-btn-2'),
      submit: document.querySelector('#form3 button[type="submit"]')
    };

    $('#rubro').on('change', function () {
      valorSeleccionado = $(this).val();
      if (valorSeleccionado === 'produccion') {
        $('#camposProduccion').slideDown();
      } else {
        $('#camposProduccion').slideUp();
        $('#costo_unitario, #precio_venta, #unidad_producida').val('');
      }
    });

    // Función para mostrar el formulario y actualizar el tab activo
    function showForm(index) {
      forms.forEach((form, i) => {
        form.classList.toggle('d-none', i !== index);
      });
      indicators.forEach((indicator, i) => {
        indicator.classList.toggle('active', i === index);
      });
    }

    // Función para validar el formulario
    function validateForm(form, button) {
      const inputs = form.querySelectorAll('input[required], select[required]');
      button.disabled = !Array.from(inputs).every(input => input.value.trim() !== '');
    }

    // Eventos de validación en tiempo real
    document.getElementById('form1').addEventListener('input', () => validateForm(document.getElementById('form1'), buttons.next1));
    document.getElementById('form2').addEventListener('input', () => validateForm(document.getElementById('form2'), buttons.next2));
    document.getElementById('form3').addEventListener('input', () => validateForm(document.getElementById('form3'), buttons.submit));

    // Eventos de navegación
    document.getElementById('next-btn-1').addEventListener('click', () => showForm(1));
    document.getElementById('back-btn-1').addEventListener('click', () => showForm(0));
    document.getElementById('next-btn-2').addEventListener('click', () => showForm(2));
    document.getElementById('back-btn-2').addEventListener('click', () => showForm(1));

    // Eventos para los tabs
    indicators.forEach((indicator, index) => {
      indicator.addEventListener('click', (e) => {
        e.preventDefault(); // Evita el comportamiento predeterminado del enlace
        showForm(index);
      });
    });

    // Evento para enviar el formulario
    document.getElementById('form3').addEventListener('submit', async function (e) {
      e.preventDefault(); // Evita el envío tradicional del formulario

      const validacionOK = await realizarCalculosFinancieros();
      if (!validacionOK) return;

      // Captura los datos de los tres formularios
      const formData1 = new FormData(document.getElementById('form1'));
      const formData2 = new FormData(document.getElementById('form2'));
      const formData3 = new FormData(document.getElementById('form3'));

      // Combina los datos en un solo objeto
      const data = {};
      formData1.forEach((value, key) => {data[key] = value;});
      formData2.forEach((value, key) => {data[key] = value;});
      formData3.forEach((value, key) => {data[key] = value;});

      // Envía los datos a la API
      $.ajax({
                    type: "POST",
                    url: "fnprestamos.php",
                    data: JSON.stringify(data),
                    contentType: "application/json", // Indicar que se envía JSON
                    success: function(response) {
                        //alert("Cliente guardado exitosamente");
                        Swal.fire({
                                    icon: 'success',
                                    title: `${response.message}`,
                                    text: ``,
                                    timer: 5000,
                                    showConfirmButton: false
                                });

                        $("#form1")[0].reset();
                        $("#form2")[0].reset();
                        $("#form3")[0].reset();
                    },
                    error: function() {
                        //alert("Hubo un error al guardar el cliente");
                        Swal.fire({
                                    icon: 'error',
                                    title: 'Hubo un error al registrar la solicitud de crédito.',
                                    text: `Si el problema persiste contacte al administrador.`,
                                    timer: 5000,
                                    showConfirmButton: false
                                });
                    }
                });

    });

    /*Buscar cliente*/
    $('#searchButton').click(function() {
            var cedula = $('#cedula').val(); // Obtiene el valor del input de cédula

            if (cedula) {
              Swal.fire({
              title: 'Buscando cliente...',
              allowOutsideClick: false,
              didOpen: () => Swal.showLoading()
               });   

                // Realiza la solicitud POST
                $.ajax({
                    url: '../clientes/fncliente.php', // Cambia esto por la URL de tu API
                    method: 'POST', // Método POST
                    contentType: 'application/json', // Tipo de contenido
                    data: JSON.stringify({ cedula: cedula }), // Envía la cédula en el cuerpo de la solicitud
                    success: function(response) {
                        // Maneja la respuesta exitosa
                        customer = response.cliente;

                        Swal.close();
                        if (response.error) {
                          Swal.fire('Error', response.message, 'error');
                          return;
                        }
                        
                        //const customer = response.cliente;
                        if (!customer) {
                          Swal.fire('No encontrado', 'No se encontró un cliente con esa cédula', 'info');
                          return;
                        }

                $("#cedula").val(customer.cedula) ,
                $("#nombre").val(customer.nombre) 
                $("#telefono").val(customer.telefono),
                $("#estado_civil").val(customer.estado_civil),
                //tipo: $("#tipo").val(),
                $("#actividad_economica").val(customer.actividad_economica),
                $("#direccion_domicilio").val(customer.direccion_domicilio),
                $("#tipo_vivienda").val(customer.tipo_vivienda),
                $("#anos_habitar").val(customer.anos_habitar),
                $("#direccion_negocio").val(customer.direccion_negocio),
                $("#tipo_local").val(customer.tipo_local),
                $("#tiempo_operar").val(customer.tiempo_operar),
                $("#rubro").val(customer.rubro),
                $("#idcliente").val(customer.idcliente);

                validateForm(document.getElementById('form1'), buttons.next1);
                    },
                    error: function(xhr, status, error) {
                        // Maneja errores
                        alert('Error en la solicitud: ' + error);
                    }
                });
            } else {
              Swal.fire('Error', 'Por favor ingrese una cédula válida', 'error'); // Validación si el campo está vacío
              return;
            }
        });
    
    function enviarDatosPromedio(tipoPromedio, ventaBuena, ventaMedia, ventaBaja) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'fnprestamos.php',
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                action: 'promedio_venta',
                tipo: tipoPromedio,
                buena: ventaBuena,
                media: ventaMedia,
                baja: ventaBaja
            }),
            success: function (response) {
                if (response.error) {
                    Swal.fire('Error', response.error, 'error');
                    reject(response.error);
                } else {
                    $('#promedio_venta').val(response.venta_promedio.toFixed(2));
                    $('#ventas_mensuales').val(response.venta_promedio.toFixed(2));
                    resolve();
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'Error al procesar la solicitud', 'error');
                reject(error);
            }
        });
    });
    }

    $('#btnCalVentaPromedio').click(function() {
        realizarCalculosFinancieros();            
    });

    function calcularIngreso() {
        // 1. Objeto para mapear los IDs y valores
        const ingresos = {
            ventasMensuales: parseFloat($('#ventas_mensuales').val()) || 0,
            otroIngresoNegocio: parseFloat($('#otros_ingresos_negocio').val()) || 0,
            aportesFamiliares: parseFloat($('#aportes_familiares').val()) || 0,
            otrosIngresos: parseFloat($('#otros_ingresos').val()) || 0
        };

        // 2. Validación más limpia
        if (Object.values(ingresos).some(isNaN)) {
            alert('Por favor ingrese valores válidos en todos los campos numéricos');
            return;
        }

        // 3. Cálculo más legible
        const totalIngreso = Object.values(ingresos).reduce((sum, value) => sum + value, 0);
        
        // 4. Formateo mejorado
        $('#total_ingresos').val(totalIngreso.toFixed(2));
    }

    const calcularGasto = () => {
      // 1. Objeto para mapear los IDs y valores
      const gastos = {
            costoVenta: parseFloat($('#gasto_costo_venta').val()) || 0,
            gastosNegocio: parseFloat($('#gastos_negocio').val()) || 0,
            cuotasCredito: parseFloat($('#cuotas_credito').val()) || 0,
            gastosFamiliares: parseFloat($('#gastos_familiares').val()) || 0
        };

        // 2. Validación más limpia
        if (Object.values(gastos).some(isNaN)) {
            alert('Por favor ingrese valores válidos en todos los campos numéricos');
            return;
        }

        // 3. Cálculo más legible
        const totalGastos = Object.values(gastos).reduce((sum, value) => sum + value, 0);
        
        // 4. Formateo mejorado
        $('#total_gastos').val(totalGastos.toFixed(2));
    }

    const cacularUtilidad = () =>{
      // 1. Objeto para mapear los IDs y valores
      const utilidad = {
            totalIngresos: parseFloat($('#total_ingresos').val()) || 0,
            totalGastos: parseFloat($('#total_gastos').val()) || 0
        };

        // 2. Validación más limpia
        if (Object.values(utilidad).some(isNaN)) {
            alert('Por favor ingrese valores válidos en todos los campos numéricos');
            return;
        }

        // 3. Cálculo más legible
        const totalUtilidad = utilidad.totalIngresos - utilidad.totalGastos;
        
        // 4. Formateo mejorado
        $('#utilidad_final').val(totalUtilidad.toFixed(2));
    }

    async function realizarCalculosFinancieros() {

        const tipo = $('#tipo_promedio').val();
        const buena = parseFloat($('#venta_promedio_bueno').val()) || 0;
        const media = parseFloat($('#venta_promedio_mediano').val()) || 0;
        const baja = parseFloat($('#venta_promedio_bajo').val()) || 0;

        if (isNaN(buena) || isNaN(media) || isNaN(baja)) {
            Swal.fire('Advertencia', 'Por favor ingrese valores válidos', 'warning');
            return false;
        }

        try {
            await enviarDatosPromedio(tipo, buena, media, baja);
            calcularIngreso();
            calcularGasto();
            cacularUtilidad();
            return true;
        } catch (error) {
            return false;
        }

    }
    // Ejecutar cuando el input de monto pierde el foco
    $('#monto_solicitado').on('blur', function() {
            var descripcion = $('#cod_cartera').val(); // Captura la descripción
            var monto = $('#monto_solicitado').val(); // Captura el monto

            if (descripcion.trim() !== '' && monto.trim() !== '') {
                $.ajax({
                    url: 'fnprestamos.php', // <-- Cambiar a tu ruta real
                    method: 'POST',
                    data: JSON.stringify({
                        descripcion: descripcion,
                        monto: monto,
                        action: 'limite_credito'
                    }),
                    dataType: 'json',
                    success: function(response) {
                        if (response.mensaje) {

                          if(response.mensaje==='El monto está por debajo del mínimo permitido.' || response.mensaje==='El monto está por encima del máximo permitido.')
                           {
                               $('#btn_enviar_solicitud').hide();

                               Swal.fire({
                                    icon: 'warning',
                                    title: `${response.mensaje}`,
                                    text: `Si persiste en ingresar el monto, contacte al administrador.`,
                                    timer: 5000,
                                    showConfirmButton: false
                                });

                           }else{
                               $('#btn_enviar_solicitud').show();
                               //alert(response.mensaje);
                           }
                         
                            

                        } else {
                            Swal.fire({
                                    icon: 'warning',
                                    title: `Respuesta inesperada.`,
                                    text: `Por favor contacte al administrador.`,
                                    timer: 5000,
                                    showConfirmButton: false
                                });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', error);
                    }
                });
            }
        });

    const calcularCostoProduccion = (ventasMensuales,rubro) => {
           // Solo enviar datos adicionales si el rubro es Producción
                  let data = {
                      action: 'estimacion_costo',
                      rubro: rubro,
                      ventasMensuales: ventasMensuales
                  };

                  if (rubro === 'produccion') {
                      data.costoUnitario = parseFloat($('#costo_unitario').val()) || 0;
                      data.precioVenta = parseFloat($('#precio_venta').val()) || 0;
                      data.unidadesProducidas = parseFloat($('#unidad_producida').val()) || 0;
                  }

                  $.ajax({
                      url: 'fnprestamos.php',
                      method: 'POST',
                      contentType: 'application/json',
                      data: JSON.stringify(data),
                      dataType: 'json',
                      success: function (response) {
                          $('#gasto_costo_venta').val(response);
                      },
                      error: function (err) {
                          $('#gasto_costo_venta').val('');
                          
                      }
                         });
        }

    function validarCamposProduccion(venta_mensual, rubro) {

                  const costo = parseFloat($('#costo_unitario').val());
                  const precio = parseFloat($('#precio_venta').val());
                  const unidades = parseFloat($('#unidad_producida').val());

                  const camposValidos = !isNaN(costo) && costo >= 0 &&
                                        !isNaN(precio) && precio >= 0 &&
                                        !isNaN(unidades) && unidades >= 0;

                  if (camposValidos) {
                    calcularCostoProduccion(venta_mensual,rubro); // Aquí llamas a tu función si los campos están bien
                  }

          }

    $('#ventas_mensuales').on('blur', function () {
                    const ventasMensuales = parseFloat($('#ventas_mensuales').val()) || 0;
                    const rubro = $('#rubro').val();

                    calcularCostoProduccion(ventasMensuales,rubro);

                    
            });

    // Asigna el evento blur a cada input
    $('#costo_unitario, #precio_venta, #unidad_producida').on('blur', function () {
              
              const ventasMensuales = parseFloat($('#ventas_mensuales').val()) || 0;
              const rubro = $('#rubro').val();

              validarCamposProduccion(ventasMensuales, rubro);

            });

  });


</script>
</body>
</html>