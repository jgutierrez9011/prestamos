<?php
require_once '../usuarios/reg.php';
require_once '../../menu_builder.php';
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
                               <input type="hidden" class="form-control form-control-sm" id="idcliente" name="idcliente" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control form-control-sm" id="telefono" name="telefono" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="estado_civil">Estado Civil</label>
                                <select class="form-control form-control-sm" id="estado_civil" name="estado_civil" required>
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
                                <select class="form-control form-control-sm" id="tipo_vivienda" name="tipo_vivienda" required>
                                    <option value="">Seleccione...</option>
                                    <option value="propio">Propio</option>
                                    <option value="renta">Renta</option>
                                    <option value="albergue">Albergue</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="anos_habitar">Años de Habitar</label>
                                <input type="number" class="form-control form-control-sm" id="anos_habitar" name="anos_habitar" min="0" required>
                            </div>
                            
                        </div>
                        <div class="form-row">
                        <div class="form-group col-md-12">
                                <label for="direccion_domicilio">Dirección del Domicilio</label>
                                <input type="text" class="form-control form-control-sm" id="direccion_domicilio" name="direccion_domicilio" required>
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
                <button type="button" id="next-btn-1" class="btn btn-primary" disabled>Siguiente</button>
                
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
                                <input type="number" class="form-control form-control-sm" id="monto_solicitado" name="monto_solicitado" required>
                            </div>
                            <div class="form-group">
                                <label for="plazoSolicitado">Plazo Solicitado (meses):</label>
                                <input type="number" class="form-control form-control-sm" id="plazo_solicitado" name="plazo_solicitado" required>
                            </div>
                            <div class="form-group">
                                <label for="tasa">Tasa (%):</label>
                                <input type="number" class="form-control form-control-sm" id="tasa" name="tasa" required>
                            </div>
                            <div class="form-group">
                                <label for="garantia">Garantía:</label>
                                <input type="text" class="form-control form-control-sm" id="garantia" name="garantia" required>
                            </div>

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
            <label for="tipoPromedio">Tipo de Promedio:</label>
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
            <input type="text" class="form-control form-control-sm" id="venta_promedio_bueno" name="venta_promedio_bueno" required>
          </div>
        </div>
        <!-- Venta Promedio Diaria Mediana -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="venta_promedio_mediano">Mediana:</label>
            <input type="text" class="form-control form-control-sm" id="venta_promedio_mediano" name="venta_promedio_mediano" required>
          </div>
        </div>
        <!-- Venta Promedio Diaria Baja -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="venta_promedio_bajo">Baja:</label>
            <input type="text" class="form-control form-control-sm" id="venta_promedio_bajo" name="venta_promedio_bajo" required>
          </div>
        </div>
        <!-- Promedio de Venta -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="promedio_venta">Promedio de Venta:</label>
            <input type="text" class="form-control form-control-sm" id="promedio_venta" name="promedio_venta" required>
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
            <input type="text" class="form-control form-control-sm" id="ventas_mensuales" name="ventas_mensuales" required>
          </div>
          <div class="form-group">
            <label for="otros_ingresos_negocio">Otros Ingresos del Negocio:</label>
            <input type="text" class="form-control form-control-sm" id="otros_ingresos_negocio" name="otros_ingresos_negocio" required>
          </div>
          <div class="form-group">
            <label for="aportes_familiares">Aportes Familiares:</label>
            <input type="text" class="form-control form-control-sm" id="aportes_familiares" name="aportes_familiares" required>
          </div>
          <div class="form-group">
            <label for="otros_ingresos">Otros Ingresos:</label>
            <input type="text" class="form-control form-control-sm" id="otros_ingresos" name="otros_ingresos" required>
          </div>
        </fieldset>
      </div>

      <!-- Columna 2: Gastos -->
      <div class="col-md-6">
        <fieldset class="border p-2">
          <legend class="w-auto">Gastos</legend>
          <div class="form-group">
            <label for="gasto_costo_venta">Gastos de Costos de Venta:</label>
            <input type="text" class="form-control form-control-sm" id="gasto_costo_venta" name="gasto_costo_venta" required>
          </div>
          <div class="form-group">
            <label for="gastos_negocio">Gastos del Negocio:</label>
            <input type="text" class="form-control form-control-sm" id="gastos_negocio" name="gastos_negocio" required>
          </div>
          <div class="form-group">
            <label for="cuotas_credito">Cuotas de Crédito:</label>
            <input type="text" class="form-control form-control-sm" id="cuotas_credito" name="cuotas_credito" required>
          </div>
          <div class="form-group">
            <label for="gastos_familiares">Gastos Familiares:</label>
            <input type="text" class="form-control form-control-sm" id="gastos_familiares" name="gastos_familiares" required>
          </div>
        </fieldset>
      </div>
    </div> <!-- Fin de la fila 2 -->

    <!-- Fila 3: Utilidad Final -->
    <div class="row mt-4"> <!-- mt-4 para agregar un margen superior -->
      <div class="col-md-12">
        <div class="form-group">
          <label for="utilidad_final">Utilidad Final:</label>
          <input type="text" class="form-control form-control-sm" id="utilidad_final" name="utilidad_final" required>
        </div>
      </div>
    </div> <!-- Fin de la fila 3 -->
                <button type="button" id="back-btn-2" class="btn btn-secondary">Atrás</button>
                <button type="submit" class="btn btn-success" disabled>Enviar</button>

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
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>
<script>
  $(function () {
    const forms = document.querySelectorAll('.form-step');
    const indicators = document.querySelectorAll('#step-indicators .nav-link');
    const buttons = {
      next1: document.getElementById('next-btn-1'),
      next2: document.getElementById('next-btn-2'),
      submit: document.querySelector('#form3 button[type="submit"]')
    };

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
    document.getElementById('form3').addEventListener('submit', function (e) {
      e.preventDefault(); // Evita el envío tradicional del formulario

      // Captura los datos de los tres formularios
      const formData1 = new FormData(document.getElementById('form1'));
      const formData2 = new FormData(document.getElementById('form2'));
      const formData3 = new FormData(document.getElementById('form3'));

      // Combina los datos en un solo objeto
      const data = {};
      formData1.forEach((value, key) => {
        data[key] = value;
      });
      formData2.forEach((value, key) => {
        data[key] = value;
      });
      formData3.forEach((value, key) => {
        data[key] = value;
      });

  

      // Envía los datos a la API


      $.ajax({
                    type: "POST",
                    url: "fnprestamos.php",
                    data: JSON.stringify(data),
                    contentType: "application/json", // Indicar que se envía JSON
                    success: function(response) {
                        alert("Cliente guardado exitosamente");
                        $("#form1")[0].reset();
                        $("#form2")[0].reset();
                        $("#form3")[0].reset();
                    },
                    error: function() {
                        alert("Hubo un error al guardar el cliente");
                    }
                });

    });


    $('#searchButton').click(function() {
            var cedula = $('#cedula').val(); // Obtiene el valor del input de cédula

            if (cedula) {
                // Realiza la solicitud POST
                $.ajax({
                    url: '../clientes/fncliente.php', // Cambia esto por la URL de tu API
                    method: 'POST', // Método POST
                    contentType: 'application/json', // Tipo de contenido
                    data: JSON.stringify({ cedula: cedula }), // Envía la cédula en el cuerpo de la solicitud
                    success: function(response) {
                        // Maneja la respuesta exitosa
                        customer = response.cliente;

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
                alert('Por favor, introduce una cédula válida.'); // Validación si el campo está vacío
            }
        });


  });
</script>
</body>
</html>