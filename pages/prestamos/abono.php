<?php
require_once  '../usuarios/reg.php';
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
<!-- INICIA EL MENU -->
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
            <h1>Abono</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Prestamos</a></li>
              <li class="breadcrumb-item active">Abonos</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>



    <!-- Main content -->
    <section class="content">

        <!-- Card de Información del Préstamo -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Información del Préstamo
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                    <p class="mb-2">
                    <strong class="font-size-base">ID Préstamo:</strong>
                    <span id="lbl_prestamo" class="badge badge-secondary">No asignado</span>
                    </p>
                    <p class="mb-2">
                    <strong class="font-size-base">Monto Aprobado:</strong>
                    <span id="lbl_monto_aprobado" class="badge badge-secondary">No asignado</span>
                    </p>
                    <p class="mb-2">
                    <strong class="font-size-base">Interés:</strong>
                    <span id="lbl_interes" class="badge badge-secondary">No asignado</span>
                    </p>
                    <p class="mb-2">
                    <strong class="font-size-base">Plazo:</strong>
                    <span id="lbl_plazo" class="badge badge-secondary">No asignado</span>
                    </p>
                    <p class="mb-2">
                    <strong class="font-size-base">Fecha de Aprobación:</strong>
                    <span id="lbl_fecha_aprobacion" class="badge badge-secondary">No asignado</span>
                    </p>
                    </div>
                    <div class="col-md-6">
                    <p class="mb-2">
                    <strong class="font-size-base">Saldo Pendiente:</strong>
                    <span id="lbl_saldo_pendiente" class="badge badge-secondary">No asignado</span>
                    </p>
                    <p class="mb-2">
                    <strong class="font-size-base">Fecha Primera Cuota:</strong>
                    <span id="lbl_primera_cuota" class="badge badge-secondary">No asignado</span>
                    </p>
                    <p class="mb-2">
                    <strong class="font-size-base">Modalidad:</strong>
                    <span id="lbl_modalidad" class="badge badge-secondary">No asignado</span>
                    </p>
                    <p class="mb-2">
                    <strong class="font-size-base">Monto por Cuota:</strong>
                    <span id="lbl_monto_cuota" class="badge badge-secondary">No asignado</span>
                    </p>
                    <p class="mb-2">
                    <strong class="font-size-base">Interés Semanal:</strong>
                    <span id="lbl_interes_semanal" class="badge badge-secondary">No asignado</span>
                    </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Formulario para Aplicar Abonos -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                Registrar Abono
            </div>
            <div class="card-body">
                <form>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="montoAbonado">Monto Abonado</label>
                            <input type="number" class="form-control" id="montoAbonado" placeholder="Ingrese el monto">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="fechaAbono">Fecha de Abono</label>
                            <input type="date" class="form-control" id="fechaAbono" value="2023-10-15">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="esProrroga">
                            <label class="form-check-label" for="esProrroga">
                                ¿Es prórroga?
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Aplicar Abono</button>
                </form>
            </div>
        </div>

        <!-- Card de Calendario de Pagos -->
        <div class="card">
            <div class="card-header bg-info text-white">
                Calendario de Pagos
            </div>
            <div class="card-body">
                <table id="tb_calendarioPago" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Semana</th>
                            <th>Fecha de Pago</th>
                            <th>Cuota</th>
                            <th>Interés</th>
                            <th>Cuota Capital</th>
                            <th>Saldo Pendiente</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- FOOTER -->
  <?php require_once '../../footer.php'; ?>
  <!-- FOOTER -->

  <!-- Modal -->
  <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">Activar/Inactivar usuario</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <form role="form" action="fnusuario.php" method="post">

                 <div class="form-group">
                     <label for="inputName">Fecha</label>
                     <input type="date" class="form-control" id="fechabaja" name="fechabaja" required/>
                 </div>

                 <div class="form-group">
                     <input type="hidden" class="form-control" id="idempleado" name="idempleado"/>
                     <input type="hidden" class="form-control" id="estado_usuario" name="estado_usuario"/>
                 </div>

                 <input type="submit" name="inactivar" id="inactivar" value="Desactivar" class="btn btn-danger"/>

                 <input type="submit" name="activar" id="activar" value="Activar" class="btn btn-success"/>

          </form>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

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
<!-- Page specific script -->
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
        url: `apiprestamo.php?id_prestamo=${id}`, // Enviar el ID como parámetro GET
        method: 'GET',
        success: function(response) {
          // Llenar los campos con los datos obtenidos
          $('#lbl_prestamo').text(response.id_prestamo);
          $('#lbl_monto_aprobado').text(response.monto_aprobado);
          $('#lbl_interes').text(response.interes);
          $('#lbl_plazo').text(response.plazo);
          $('#lbl_fecha_aprobacion').text(response.fecha_aprobacion);
          $('#lbl_saldo_pendiente').text(response.saldo);
          $('#lbl_primera_cuota').text(response.fecha_primer_cuota);
          $('#lbl_modalidad').text(response.modalidad);
          $('#lbl_monto_cuota').text(response.monto_cuota);
          $('#lbl_interes_semanal').text(response.interes_semanal);
          
        },
        error: function() {
          alert('Hubo un error al cargar los datos.');
        }
      });
    }

    $('#tb_calendarioPago').DataTable({
        ajax: {
            url: `servicio_calendariopago.php?id_solicitud=${id}`,
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.log("Error en la carga de datos: ", error);
                console.log("Estado: ", xhr.status);
                console.log("Respuesta: ", xhr.responseText);
            }
        },
        columns: [
                { data: "modalidad"},
                { data: "fecha_pago"},
                { data: "monto_cuota"},
                { data: "interes"},
                { data: "principal"},
                { data: "saldo"}
              ],
        initComplete: function(settings, json) {
        // Verificar si hay datos en la respuesta
                  if (json && json.data && json.data.length > 0) {
                disableActionButtons("La solicitud ya fue aprobada.");
                    Swal.fire({
                icon: 'success',
                title: 'La solicitud de crédito esta aprobada.',
                text: `Hay ${json.data.length} pagos programados`,
                timer: 5000,
                showConfirmButton: false
            });
                  } else {
                    Swal.fire({
                icon: 'warning',
                title: 'La solicitud de crédito esta pendiente.',
                text: 'No se encontraron pagos programados. Se debe revisar.',
                confirmButtonText: 'Entendido'
            });
                  }
        }
    });
    
    loadData(id);
});

</script>

<script>

$(document).ready(function(){

  $(document).on('click', '.edit_data', function(){

       var employee_id = $(this).attr("id");
       var estado = $("#estado_" + employee_id).val();

       $('#idempleado').val(employee_id);
       $('#estado_usuario').val(estado);

         if(estado == '1'){
           $("#inactivar").show();
           $("#activar").hide();
         }else {
           $("#inactivar").hide();
           $("#activar").show();
         }

       });

  });

</script>
</body>
</html>
