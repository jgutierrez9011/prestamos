<?php
require_once  '../usuarios/reg.php';
require_once 'fncloud.php';

if(isset($_POST["btnloadodin"])){

    load_file_odin();

}

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
            <h1>Carga de clientes cloud</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Procesos</a></li>
              <li class="breadcrumb-item active">Cloud</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>



    <!-- Main content -->
    <section class="content">

    <div class="row">
        <div class="col-md-12 mb-12">
        <?php
      if(isset($_GET["token"])):
        if($_GET["token"] == 1):
          echo "<div class='alert alert-success'>
               <strong>Exito!</strong> El archivo ha sido cargado correctamente!
               </div>";
        elseif($_GET["token"] == 2):
          echo "<div class='alert alert-warning'>
                <strong>Error!</strong> Disculpe no se cargo el archivo!, por favor verifique.
                </div>";
        endif;
      endif;
         ?>
       </div>
      </div>

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Buscar archivo</h3>

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

        <div class="container mt-3">
            <p>Busque el reporte de clientes cloud en formato de excel con el nombre de "Reporte Cloud Nicaragua.xlsx" y seleccione para insertar en la base de datos de AINP:</p>
            <form action="cloud.php" method="post" enctype="multipart/form-data">
                
                <p>Reporte de clientes con servicio cloud de odin:</p>
                <input type="file" id="rptodin" name="rptodin" accept=".xlsx, .xls">
            
                <div class="mt-3">
                <button type="submit" id ="btnloadodin" name="btnloadodin" class="btn btn-primary">Importar</button>
                </div>
            </form>

            <form method="post" name="ejec_py" id="ejec_py">
               <?php if(isset($_GET["token"]) && $_GET["token"] == 1) {?>
                
                <div class="mt-3">
                <button type="submit" id ="btnexepython" name="btnexepython" class="btn btn-primary">Cargar</button>
                </div>

               <?php } ?>

            </form>
        </div>

        <br>

        <div class="row">
        
            <div id="loader_script"></div>

        </div>
        
        <div class="row">
            <p>Salida del script:</p>
            <br>
            <textarea class="form-control" id="output" rows="10" cols="100" readonly></textarea>
        </div>

        </div>

        <br>

        

    

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

    
    $( "#ejec_py" ).submit(function( event ) {

           $('#loader_script').html('<img src="../image/ajax-loader.gif" alt="loading"/> Ejecutando script y cargando salida, espere por favor...');
            // Limpiar textarea antes de ejecutar
            $('#output').val('');
            
            var parametros = $(this).serialize();
            // Realizar la solicitud AJAX
            $.ajax({
                type: 'POST',
                url: 'fncloud.php',
                data:{"exec":1},
                success: function(data) {
                    // Mostrar la salida en el textarea
                    $('#loader_script').html('');
                    $('#output').val(data);
                },
                error: function(xhr, status, error) {
                    alert('Error al ejecutar el script de Python.');
                    console.error(xhr.responseText);
                }
            });
            event.preventDefault();
        });
        
    
  });
</script>
</body>
</html>