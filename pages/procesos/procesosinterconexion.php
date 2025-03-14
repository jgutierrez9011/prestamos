<?php
require_once  '../usuarios/reg.php';
require_once '../cn.php';
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
            <h1>Procesos de servicio interconexion</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Procesos</a></li>
              <li class="breadcrumb-item active">servicios interconexion</li>
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
          <h3 class="card-title">Listado</h3>

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
                     <a href="usuarios.php" class="btn btn-primary" role="button">Agregar procedimiento</a>
                  </div>
            </div>

           </form>
           <br>
           <div class="row">
		   
		   <div class='row table-responsive'>
                             <table class='table table-bordered table-striped' id='mitabla' style='width:100%'>
                         <thead>
                         <tr>
                         <th><p class='small'><strong>ID PROCESO</strong></p></th>
                         <th><p class='small'><strong>PROCESO ORACLE</strong></p></th>
                         <th><p class='small'><strong>PROCESO UNIX</strong></p></th>
                         <th><p class='small'><strong>OPCIONES</strong></p></th>
                         <th><p class='small'><strong>ESTADO</strong></p></th>
                         <th><p class='small'><strong>DESCRIPCION</strong></p></th>
                         <th><p class='small'><strong>LOG</strong></p></th>


                         </tr>
                         </thead>
                         <tbody>
             <?php
			 
			 $conn = conexion_bd(2);

             $query = "SELECT ID, NOMBRECORTO, NOMBRELARGO, LOG, DESCRIPCION, ESTADO, FECHA 
             FROM TBLPROCESOS
             WHERE UPPER(TRIM(ID)) LIKE '%ICX%'
             ORDER BY ID ASC";
												 
             $stmt = $conn->prepare($query);
             $stmt->execute();
			 
			 $data = array();
		     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ;?> 
                     <tr>
                      <td><a><?php echo $row['ID']; ?></a></td>
                      <td><a><?php echo $row['NOMBRECORTO'];?></a><br><small>Ejecutado: <?php echo $row['FECHA'];?></small></td>
										  <td><?php echo $row['NOMBRELARGO'];?></td>
										  <td>
                      
                      </td>
										  <td>
                      <div class="progress progress-xs">
                          <?php if ($row['ESTADO'] <> 100) {;?>
                          <div class="progress-bar bg-danger" style="width: <?php echo $row['ESTADO'];?>%"></div>
                          <?php }else {;?>
                          <div class="progress-bar bg-success" style="width: <?php echo $row['ESTADO'];?>%"></div>
                          <?php };?>
                      </div>
                      <br><small><?php echo $row['ESTADO'];?>% Completado</small>
                      </td>
                      <td><?php echo $row['DESCRIPCION'];?></td>
                      <td><?php echo $row['LOG'];?></td>
                     </tr>
                                          

              
			   <?php }; ?>
			   </tbody>
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
    $("#mitabla").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
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