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
            <h1>Catálago de clientes</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Catalogo</a></li>
              <li class="breadcrumb-item active">Clientes</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>



    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Buscar cliente</h3>

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

           <form class="needs-validation" method="post" action="">
             <!-- Primera linea de campos en el fromulario-->

            <div class="row">
                  <div class="col-md-4">
                     <a href="nuevo_cliente.php" class="btn btn-primary" role="button">Nuevo cliente</a>
                  </div>
            </div>

           </form>
           <br>
           <div class="row">
        <div class="row table-responsive">
          <table id="clientesTable" class="table table-bordered table-striped" style="width:100%">
              <thead>
                  <tr>
                      <th><p class="small"><strong>ID</strong></p></th>
                      <th><p class="small"><strong>Acciones</strong></p></th>
                      <th><p class="small"><strong>Cedula</strong></p></th>
                      <th><p class="small"><strong>Nombre</strong></p></th>
                      <th><p class="small"><strong>Telefono</strong></p></th>
                      <th><p class="small"><strong>Estado civil</strong></p></th>
                      <th><p class="small"><strong>Actividad economica</strong></p></th>
                      <th><p class="small"><strong>Direccion domicilio</strong></p></th>
                      <th><p class="small"><strong>Tipo vivienda</strong></p></th>
                      <th><p class="small"><strong>Años habitando</strong></p></th>
                      <th><p class="small"><strong>Dirección negocio</strong></p></th>
                      <th><p class="small"><strong>Tipo local</strong></p></th>
                      <th><p class="small"><strong>Tiempo operando</strong></p></th>
                      <th><p class="small"><strong>Rubro</strong></p></th>
                  </tr>
              </thead>
          </table>
        </div>

         <br>
           </div>

        </div>
        <!-- /.card-body -->
        <div class="card-footer">

        </div>
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

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
            url: 'fncliente.php',
            dataSrc: '',
            error: function(xhr, error, thrown) {
                console.log("Error en la carga de datos: ", error);
                console.log("Estado: ", xhr.status);
                console.log("Respuesta: ", xhr.responseText);
            }
        },
        columns: [
            { data: "idcliente" },
            {
                data: "idcliente",
                render: function(data, type, row) {
                return `
                    <a href="editar_cliente.php?idcliente=${data}" class="btn btn-sm btn-primary">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                `;
            },
                orderable: false,
                searchable: false
            },
            { data: "cedula" },
            { data: "nombre" },
            { data: "telefono" },
            { data: "estado_civil" },
            { data: "actividad_economica" },
            { data: "direccion_domicilio" },
            { data: "tipo_vivienda" },
            { data: "anos_habitar" },
            { data: "direccion_negocio" },
            { data: "tipo_local" },
            { data: "tiempo_operar" },
            { data: "rubro" }
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
