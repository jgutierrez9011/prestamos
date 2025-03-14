<?php
require_once '../usuarios/reg.php';
require_once 'solicitud_service.php';

try {
  $pdo = $base_de_datos;
  $solicitudBL = new SolicitudPrestamo($pdo);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
  exit;
}

if (isset($_GET['id_solicitud'])) {
  $id_solicitud = $_GET['id_solicitud'];
  //Cambia el estado de la solicitud a En revision
  $estadoSolicitud = $solicitudBL->updateSolicitudEstado($_SESSION["idusuario"],2,$id_solicitud);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Consulta de Créditos</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
  <?php require_once '../../menu.php'; ?>
  <!-- TERMINA EL MENU -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Consulta de Créditos</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Créditos</a></li>
              <li class="breadcrumb-item active">Consulta de Créditos</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row justify-content-center">
        <div class="col-md-10">
          <!-- Sección para mostrar la información del cliente -->
          <div id="client-info" class="card">
            <div class="card-header">
              <h3 class="card-title">Información del Cliente</h3>
              <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
            </div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="cedula">Cédula</label>
                  <input type="text" class="form-control form-control-sm" id="cedula" name="cedula" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="nombre">Nombre</label>
                  <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="telefono">Teléfono</label>
                  <input type="text" class="form-control form-control-sm" id="telefono" name="telefono" readonly>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="estado_civil">Estado Civil</label>
                  <input type="text" class="form-control form-control-sm" id="estado_civil" name="estado_civil" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="tipo_vivienda">Tipo de Vivienda</label>
                  <input type="text" class="form-control form-control-sm" id="tipo_vivienda" name="tipo_vivienda" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="anos_habitar">Años de Habitar</label>
                  <input type="text" class="form-control form-control-sm" id="anos_habitar" name="anos_habitar" readonly>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="direccion_domicilio">Dirección del Domicilio</label>
                  <input type="text" class="form-control form-control-sm" id="direccion_domicilio" name="direccion_domicilio" readonly>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="actividad_economica">Actividad Económica</label>
                  <input type="text" class="form-control form-control-sm" id="actividad_economica" name="actividad_economica" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="rubro">Rubro</label>
                  <input type="text" class="form-control form-control-sm" id="rubro" name="rubro" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="tipo_local">Tipo de Local</label>
                  <input type="text" class="form-control form-control-sm" id="tipo_local" name="tipo_local" readonly>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="tiempo_operar">Tiempo de Operar</label>
                  <input type="text" class="form-control form-control-sm" id="tiempo_operar" name="tiempo_operar" readonly>
                </div>
                <div class="form-group col-md-8">
                  <label for="direccion_negocio">Dirección del Negocio</label>
                  <input type="text" class="form-control form-control-sm" id="direccion_negocio" name="direccion_negocio" readonly>
                </div>
              </div>
            </div>
          </div>

          <!-- Sección para mostrar la información del crédito -->
          <div id="credit-info" class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Información del Crédito</h3>
            <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
        </div>
        <div class="card-body">
            <!-- Fila 1: Monto, Plazo y Tasa -->
            <div class="row">
            <!-- Monto -->
            <div class="col-md-4">
                <div class="form-group">
                <label for="monto_solicitado">Monto Solicitado:</label>
                <input type="text" class="form-control form-control-sm" id="monto_solicitado" name="monto_solicitado" readonly>
                </div>
            </div>
            <!-- Plazo -->
            <div class="col-md-4">
                <div class="form-group">
                <label for="plazo_solicitado">Plazo Solicitado (meses):</label>
                <input type="text" class="form-control form-control-sm" id="plazo_solicitado" name="plazo_solicitado" readonly>
                </div>
            </div>
            <!-- Tasa -->
            <div class="col-md-4">
                <div class="form-group">
                <label for="tasa">Tasa (%):</label>
                <input type="text" class="form-control form-control-sm" id="tasa" name="tasa" readonly>
                </div>
            </div>
            </div> <!-- Fin de la fila 1 -->

            <!-- Fila 2: Garantía -->
            <div class="row mt-3"> <!-- mt-3 para agregar un margen superior -->
            <div class="col-md-12">
                <div class="form-group">
                <label for="garantia">Garantía:</label>
                <input type="text" class="form-control form-control-sm" id="garantia" name="garantia" readonly>
                </div>
            </div>
            </div> <!-- Fin de la fila 2 -->
        </div> <!-- Cierre del card-body -->
          </div>

          <!-- Sección para mostrar la información financiera -->
          <div id="financial-info" class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">Información Financiera</h3>
    <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
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
            <label for="tipo_promedio">Tipo de Promedio:</label>
            <input type="text" class="form-control form-control-sm" id="tipo_promedio" name="tipo_promedio" readonly>
          </div>
        </div>
        <!-- Venta Promedio Diaria Buena -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="venta_promedio_bueno">Buena:</label>
            <input type="text" class="form-control form-control-sm" id="venta_promedio_bueno" name="venta_promedio_bueno" readonly>
          </div>
        </div>
        <!-- Venta Promedio Diaria Mediana -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="venta_promedio_mediano">Mediana:</label>
            <input type="text" class="form-control form-control-sm" id="venta_promedio_mediano" name="venta_promedio_mediano" readonly>
          </div>
        </div>
        <!-- Venta Promedio Diaria Baja -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="venta_promedio_bajo">Baja:</label>
            <input type="text" class="form-control form-control-sm" id="venta_promedio_bajo" name="venta_promedio_bajo" readonly>
          </div>
        </div>
        <!-- Promedio de Venta -->
        <div class="col-md-2">
          <div class="form-group">
            <label for="promedio_venta">Promedio de Venta:</label>
            <input type="text" class="form-control form-control-sm" id="promedio_venta" name="promedio_venta" readonly>
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
            <input type="text" class="form-control form-control-sm" id="ventas_mensuales" name="ventas_mensuales" readonly>
          </div>
          <div class="form-group">
            <label for="otros_ingresos_negocio">Otros Ingresos del Negocio:</label>
            <input type="text" class="form-control form-control-sm" id="otros_ingresos_negocio" name="otros_ingresos_negocio" readonly>
          </div>
          <div class="form-group">
            <label for="aportes_familiares">Aportes Familiares:</label>
            <input type="text" class="form-control form-control-sm" id="aportes_familiares" name="aportes_familiares" readonly>
          </div>
          <div class="form-group">
            <label for="otros_ingresos">Otros Ingresos:</label>
            <input type="text" class="form-control form-control-sm" id="otros_ingresos" name="otros_ingresos" readonly>
          </div>
        </fieldset>
      </div>

      <!-- Columna 2: Gastos -->
      <div class="col-md-6">
        <fieldset class="border p-2">
          <legend class="w-auto">Gastos</legend>
          <div class="form-group">
            <label for="gasto_costo_venta">Gastos de Costos de Venta:</label>
            <input type="text" class="form-control form-control-sm" id="gasto_costo_venta" name="gasto_costo_venta" readonly>
          </div>
          <div class="form-group">
            <label for="gastos_negocio">Gastos del Negocio:</label>
            <input type="text" class="form-control form-control-sm" id="gastos_negocio" name="gastos_negocio" readonly>
          </div>
          <div class="form-group">
            <label for="cuotas_credito">Cuotas de Crédito:</label>
            <input type="text" class="form-control form-control-sm" id="cuotas_credito" name="cuotas_credito" readonly>
          </div>
          <div class="form-group">
            <label for="gastos_familiares">Gastos Familiares:</label>
            <input type="text" class="form-control form-control-sm" id="gastos_familiares" name="gastos_familiares" readonly>
          </div>
        </fieldset>
      </div>
    </div> <!-- Fin de la fila 2 -->

    <!-- Fila 3: Utilidad Final -->
    <div class="row mt-4"> <!-- mt-4 para agregar un margen superior -->
      <div class="col-md-12">
        <div class="form-group">
          <label for="utilidad_final">Utilidad Final:</label>
          <input type="text" class="form-control form-control-sm" id="utilidad_final" name="utilidad_final" readonly>
        </div>
      </div>
    </div> <!-- Fin de la fila 3 -->
  </div> <!-- Cierre del card-body -->
          </div>

          <!-- Sección para mostrar la informacion de calendario de pago propuesto -->
          <div id="credit-amortizado" class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Tabla de amortización</h3>
            <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
        </div>
        <div class="card-body">


            <!-- Fila 1: Boton para cargar tabla de amortizacion -->
            <div class="row"> <!-- mt-3 para agregar un margen superior -->
            <button id="cargarTabla" type="button" class="btn btn-primary btn-sm ml-2">
                <i class=""></i> Cargar tabla
              </button>
              
            </div> <!-- Fin de la fila 1 -->

            <br>

            <!-- Fila 2:-->
             
            <div class="row">
            
            <div class="row table-responsive">
          <table id="amortizacionTable" class="table table-bordered table-striped" style="width:100%">
              <thead>
                  <tr>
                      <th><p class="small"><strong>Semana</strong></p></th>
                      <th><p class="small"><strong>Fecha de pago</strong></p></th>
                      <th><p class="small"><strong>Cuota</strong></p></th>
                      <th><p class="small"><strong>Interes</strong></p></th>
                      <th><p class="small"><strong>Abono a capital</strong></p></th>
                      <th><p class="small"><strong>Saldo pendiente</strong></p></th>
                  </tr>
              </thead>
          </table>
        </div>
            
            </div> <!-- Fin de la fila 1 -->

            
        </div> <!-- Cierre del card-body -->
          </div>

          <!-- Sección para mostrar las acciones -->
        <div id="actions-card" class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Acciones</h3>
        </div>
        <div class="card-body">
            <!-- Fila de botones -->
            <div class="row">
            <div class="col-md-12 text-center">
                <!-- Botón Buscar -->
                <!--<button type="button" class="btn btn-primary btn-sm">
                <i class="fas fa-search"></i> Buscar
                </button>-->
                <a href="creditos.php" class="btn btn-primary btn-sm" role="button"><i class="fas fa-search"></i> Buscar</a>
                <!-- Botón Aprobar -->
                <button type="button" class="btn btn-success btn-sm ml-2"
                data-toggle="modal" data-target="#prestamoModal">
                <i class="fas fa-check"></i> Aprobar
                </button>
                <!-- Botón Rechazar -->
                <button type="button" class="btn btn-danger btn-sm ml-2">
                <i class="fas fa-times"></i> Rechazar
                </button>
                <!-- Botón Cancelar -->
                <button type="button" class="btn btn-secondary btn-sm ml-2">
                <i class="fas fa-ban"></i> Cancelar
                </button>
            </div>
            </div> <!-- Fin de la fila de botones -->
        </div> <!-- Cierre del card-body -->
        </div>

        </div>

        
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Modal -->
  <div class="modal fade" id="prestamoModal" tabindex="-1" aria-labelledby="prestamoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prestamoModalLabel">Resolución de comite</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form id="prestamoForm">
                        <div class="form-group">
                            <label for="id_solicitud">ID Solicitud:</label>
                            <input type="number" class="form-control" id="id_solicitud" name="id_solicitud" readonly required>
                        </div>
                        <div class="form-group">
                            <label for="monto_aprobado">Monto Aprobado:</label>
                            <input type="number" class="form-control" id="monto_aprobado" name="monto_aprobado" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="interes">Interés (%):</label>
                            <input type="number" class="form-control" id="interes" name="interes" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="plazo">Plazo (Meses):</label>
                            <input type="number" class="form-control" id="plazo" name="plazo" required>
                        </div>
                        <div class="form-group">
                            <label for="cuota">Cuota:</label>
                            <input type="number" class="form-control" id="cuota" name="cuota" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="primer_cuota">Fecha primer cuota:</label>
                            <input type="date" class="form-control" id="primer_cuota" name="primer_cuota" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="comentario">Comentario:</label>
                            <textarea class="form-control" rows="5" id="comentario" name="comentario"></textarea>
                        </div>
                    </form>

                          <!-- Sección para mostrar la informacion de calendario de pago propuesto -->
          <div id="tb-credit-amortizado" class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Tabla de amortización</h3>
            <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
        </div>
        <div class="card-body">


            <!-- Fila 1: Boton para cargar tabla de amortizacion -->
            <div class="row"> <!-- mt-3 para agregar un margen superior -->
            <button id="cargar_tb_amortizado" type="button" class="btn btn-primary btn-sm ml-2">
                <i class=""></i> Cargar tabla
              </button>
              
            </div> <!-- Fin de la fila 1 -->

            <br>

            <!-- Fila 2:-->
             
            <div class="row">
            
            <div class="row table-responsive">
          <table id="amortizacionTb" class="table table-bordered table-striped" style="width:100%">
              <thead>
                  <tr>
                      <th><p class="small"><strong>Semana</strong></p></th>
                      <th><p class="small"><strong>Fecha de pago</strong></p></th>
                      <th><p class="small"><strong>Cuota</strong></p></th>
                      <th><p class="small"><strong>Interes</strong></p></th>
                      <th><p class="small"><strong>Abono a capital</strong></p></th>
                      <th><p class="small"><strong>Saldo pendiente</strong></p></th>
                  </tr>
              </thead>
          </table>
        </div>
            
            </div> <!-- Fin de la fila 1 -->

            
        </div> <!-- Cierre del card-body -->
          </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="prestamoForm" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
<!--Cierra el modal-dialog -->

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
<!-- DataTables  & Plugins -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>

<script>
  $(function () {
    // Obtener el parámetro de la URL (id o cédula)
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id_solicitud'); // Obtener el valor del parámetro 'id'

    // Cargar los datos guardados
    function loadData(id) {
      if (!id) {
        alert('No se proporcionó un ID válido.');
        return;
      }

      // Realizar la solicitud AJAX
      $.ajax({
        url: `fnprestamos.php?id_solicitud=${id}`, // Enviar el ID como parámetro GET
        method: 'GET',
        success: function(response) {
          // Llenar los campos con los datos obtenidos
          $('#id_solicitud').val(response.id_solicitud);
          $('#cedula').val(response.cedula);
          $('#nombre').val(response.nombre);
          $('#telefono').val(response.telefono);
          $('#estado_civil').val(response.estado_civil);
          $('#tipo_vivienda').val(response.tipo_vivienda);
          $('#anos_habitar').val(response.anos_habitar);
          $('#direccion_domicilio').val(response.direccion_domicilio);
          $('#actividad_economica').val(response.actividad_economica);
          $('#rubro').val(response.rubro);
          $('#tipo_local').val(response.tipo_local);
          $('#tiempo_operar').val(response.tiempo_operar);
          $('#direccion_negocio').val(response.direccion_negocio);
          $('#monto_solicitado').val(response.monto_solicitado);
          $('#plazo_solicitado').val(response.plazo_solicitado);
          $('#tasa').val(response.tasa);
          $('#garantia').val(response.garantia);
          $('#venta_promedio_bueno').val(response.venta_promedio_bueno);
          $('#venta_promedio_mediano').val(response.venta_promedio_mediano);
          $('#venta_promedio_bajo').val(response.venta_promedio_bajo);
          $('#promedio_venta').val(response.promedio_venta);
          $('#tipo_promedio').val(response.tipo_promedio);
          $('#ventas_mensuales').val(response.ventas_mensuales);
          $('#otros_ingresos_negocio').val(response.otros_ingresos_negocio);
          $('#aportes_familiares').val(response.aportes_familiares);
          $('#otros_ingresos').val(response.otros_ingresos);
          $('#gasto_costo_venta').val(response.gasto_costo_venta);
          $('#gastos_negocio').val(response.gastos_negocio);
          $('#cuotas_credito').val(response.cuotas_credito);
          $('#gastos_familiares').val(response.gastos_familiares);
          $('#utilidad_final').val(response.utilidad_final);

          $("#monto_aprobado").val(response.monto_solicitado),
          $("#interes").val(response.tasa),
          $("#plazo").val(response.plazo_solicitado)
        },
        error: function() {
          alert('Hubo un error al cargar los datos.');
        }
      });
    }

    // Cargar los datos al iniciar la página
    loadData(id);


    $('#cargar_tb_amortizado').on('click', function() {
        if ($.fn.DataTable.isDataTable('#amortizacionTb')) {
            $('#amortizacionTb').DataTable().destroy();
        }
        $('#amortizacionTb').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: 'service_amortizacion.php',
                type: 'POST',
                contentType: 'application/json',
                data: function() {
                    return JSON.stringify({
                        id_solicitud: $("#id_solicitud").val(),
                        monto_aprobado: $("#monto_aprobado").val(),
                        interes: $("#interes").val(),
                        fecha_primer_cuota: $("#primer_cuota").val(),
                        plazo: $("#plazo").val()
                    });
                },
                dataSrc: '',
                error: function(xhr, error, thrown) {
                    console.error("Error en la carga de datos: ", error);
                    console.error("Estado: ", xhr.status);
                    console.error("Respuesta: ", xhr.responseText);
                }
            },
            columns: [
                { data: "semana", title: "Semana" },
                { data: "fecha_pago", title: "Fecha de pago" },
                { data: "cuota", title: "Cuota" },
                { data: "interes", title: "Interés" },
                { data: "abono_capital", title: "Cuota a capital" },
                { data: "saldo_pendiente", title: "Saldo pendiente" }
            ]
        });
    });
    


    // creacion de prestamos
    $("#prestamoForm").submit(function(event) {
                
                event.preventDefault();
                
                // Crear un objeto con los datos del formulario
                var formData = {
                id_solicitud: $("#id_solicitud").val(),
                monto_aprobado: $("#monto_aprobado").val(),
                interes: $("#interes").val(),
                plazo: $("#plazo").val(),
                saldo: $("#cuota").val(),
                fecha_primer_cuota: $("#primer_cuota").val(),
                comentario: $("#comentario").val()
            };
                
                // Convertir el objeto a JSON
                var jsonData = JSON.stringify(formData);
                
                $.ajax({
                    type: "POST",
                    url: "apiprestamo.php",
                    data: jsonData,
                    contentType: "application/json", // Indicar que se envía JSON
                    success: function(response) {
                        alert("Prestamo registrado exitosamente");
                        //$("#clienteForm")[0].reset();
                    },
                    error: function() {
                        alert("Hubo un error al registrar el prestamo");
                    }
                });
            });
  });
</script>
</body>
</html>