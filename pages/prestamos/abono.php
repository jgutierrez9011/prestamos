<?php
require_once  '../usuarios/reg.php';
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
<?php require_once '../../menu.php'; ?>
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
                        <p><strong>ID Préstamo:</strong> 12345</p>
                        <p><strong>Monto Aprobado:</strong> $10,000.00</p>
                        <p><strong>Interés:</strong> 5.00%</p>
                        <p><strong>Plazo:</strong> 12 meses</p>
                        <p><strong>Fecha de Aprobación:</strong> 2023-10-01</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Saldo Pendiente:</strong> $8,500.00</p>
                        <p><strong>Fecha Primera Cuota:</strong> 2023-11-01</p>
                        <p><strong>Modalidad:</strong> Semanal</p>
                        <p><strong>Monto por Cuota:</strong> $875.00</p>
                        <p><strong>Interés Semanal:</strong> $50.00</p>
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
                <table class="table table-bordered">
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
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>2023-11-01</td>
                            <td>$875.00</td>
                            <td>$50.00</td>
                            <td>$825.00</td>
                            <td>$9,175.00</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>2023-11-08</td>
                            <td>$875.00</td>
                            <td>$50.00</td>
                            <td>$825.00</td>
                            <td>$8,350.00</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>2023-11-15</td>
                            <td>$875.00</td>
                            <td>$50.00</td>
                            <td>$825.00</td>
                            <td>$7,525.00</td>
                        </tr>
                        <!-- Más filas según el plazo -->
                    </tbody>
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
    $('#clientesTable').DataTable({
        ajax: {
            url: 'fnprestamos.php',
            dataSrc: '',
            error: function(xhr, error, thrown) {
                console.log("Error en la carga de datos: ", error);
                console.log("Estado: ", xhr.status);
                console.log("Respuesta: ", xhr.responseText);
            }
        },
        columns: [
            { data: "cod_solicitud" },
            { data: "nombre" },
            { data: "fecha_solicitud" },
            { data: "monto_solicitado" },
            { data: "estatus" },
            { data: "plazo_solicitado" },
            { data: "tasa" },
            { data: "oficial_credito" },
            {
                data: "cod_solicitud",
                render: function(data, type, row) {
                return `
                    <a href="consultar_solicitud.php?id_solicitud=${data}" class="btn btn-sm btn-primary">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                `;
            },
                orderable: false,
                searchable: false
            },
            {
    data: "cod_solicitud",
    render: function(data, type, row) {
        return `
            <a href="consultar_solicitud.php?id_solicitud=${data}" 
               class="btn btn-sm btn-success" 
               data-toggle="tooltip" 
               data-placement="top" 
               title="Aplicar Pago">
                <i class="fas fa-money-bill-wave"></i>
            </a>
        `;
    }
}
            
        ]
    });

    /*$(document).on('click', '.edit-btn', function() {
        var clienteId = $(this).data('id');
        alert('ID del Cliente: ' + clienteId);
    });*/
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
