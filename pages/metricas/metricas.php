<?php
require_once  '../usuarios/reg.php';
require_once 'fnmetricas.php'

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Metricas</title>

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
            <h1>Metricas</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Metricas</a></li>
              <li class="breadcrumb-item active">Metricas</li>
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
          <h3 class="card-title">Buscar por</h3>

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

        <form action="metricas.php" method="post">
        
        <div class="row">

            <div class="col-md-3 mb-3">
                <label for="Sexo">Metrica</label>
                <select class="form-control form-control-sm" id="txtmetrica" name="txtmetrica" required>
                <?php
                          $json = fn_lista_metricas();
                          $data = json_decode($json,true);
                          foreach($data as $row){
                          ?>
                          <option value="<?php echo $row["id"]; ?>"><?php echo $row["text"]; ?></option>
                          <?php }  ?>
                          </select>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="Sexo">Periodo</label>
                <select class="form-control form-control-sm" id="txtperfil" name="txtperfil" required>
                <?php
                          $json = fn_lista_periodos();
                          $data = json_decode($json,true);
                          foreach($data as $row){
                          ?>
                          <option value="<?php echo $row["id"]; ?>"><?php echo $row["text"]; ?></option>
                          <?php }  ?>
                          </select>
                </select>
            </div>


        </div>

        <div class="row">

              <div class="col-md-3 mb-3">
                <button class="btn btn-primary btn-sm" name="btnbuscar" type="submit" value="btbuscar">Buscar</button>
              </div>

        </div>

        </form>

        </div>
        <!-- /.card-body -->
        <div class="card-footer">
          
        </div>
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Detalle</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
        <!--  <table id="example1" class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>PERIODO</th>
              <th>DATO16</th>
              <th>FECHADATO16</th>
              <th>DATO48</th>
              <th>FECHADATO48</th>
              <th>DIFERENCIA</th>
            </tr>
            </thead>
            <tbody> -->
              <?php
              //$json = view_metric();
              //$json = fn_consultar_metricas();
              //$data = json_decode($json,true);
              //foreach($data as $row){
                if (isset($_POST['txtmetrica']) && isset($_POST['txtperfil'])) 
                    {

                     echo fn_consultar_metricas($_POST['txtmetrica'],$_POST['txtperfil']);

                    }
              ?>
           <!-- <tr>
              <td><?php //echo $row["PERIODO"]; ?></td>
              <td><?php //echo $row["VALOR_DATO_16"]; ?></td>
              <td><?php //echo $row["FECHA_CARGA_16"]; ?></td>
              <td><?php //echo $row["VALOR_DATO_48"]; ?></td>
              <td><?php //echo $row["FECHA_CARGA_48"]; ?></td>
              <td><?php //echo $row["DIFERENCIA"]; ?></td>
            </tr> -->
          <?php //}  ?>
          <!--  </tbody>
            <tfoot>
            <tr>
              <th>PERIODO</th>
              <th>DATO16</th>
              <th>FECHADATO16</th>
              <th>DATO48</th>
              <th>FECHADATO48</th>
              <th>DIFERENCIA</th>
            </tr>
            </tfoot>
          </table> -->
        </div>
        <!-- /.card-body -->
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
    $("#example1").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

    $('#example2').DataTable({
      "paging": false,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>
</body>
</html>
