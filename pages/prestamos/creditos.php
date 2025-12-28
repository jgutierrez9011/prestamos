<?php
require_once  '../usuarios/reg.php';
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
  <title><?php require_once '../../titulo.php'; ?> | Creditos</title>

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
            <h1>Créditos</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Prestamos</a></li>
              <li class="breadcrumb-item active">Creditos</li>
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
          <h3 class="card-title">Buscar créditos</h3>

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
                     <a href="solicitud_prestamos.php" class="btn btn-primary" role="button">Nuevo crédito</a>
                  </div>
            </div>

           </form>
           <br>

           <div class="row mb-3">
  <div class="col-md-3">
    <label for="filtro_id">Código Solicitud</label>
    <input type="number" id="filtro_id" class="form-control" placeholder="Ej. SOL123">
  </div>
  <div class="col-md-3">
    <label for="filtro_fecha">Fecha</label>
    <input type="date" id="filtro_fecha" class="form-control">
  </div>
  <div class="col-md-3">
    <label for="filtro_cliente">Cliente</label>
    <input type="text" id="filtro_cliente" class="form-control" placeholder="Nombre del cliente">
  </div>
  <div class="col-md-3">
    <label>&nbsp;</label>
    <button id="btnBuscar" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Buscar</button>
  </div>
</div>


           <div class="row">
        <div class="row table-responsive">
          <table id="clientesTable" class="table table-bordered table-striped" style="width:100%">
              <thead>
                  <tr>
                      <th><p class="small"><strong>Código</strong></p></th>
                      <th><p class="small"><strong>Cartera</strong></p></th>
                      <th><p class="small"><strong>Cliente</strong></p></th>
                      <th><p class="small"><strong>Fecha Solicitud</strong></p></th>
                      <th><p class="small"><strong>Monto Solicitado</strong></p></th>
                      <th><p class="small"><strong>Estatus</strong></p></th>
                      <th><p class="small"><strong>Plazo</strong></p></th>
                      <th><p class="small"><strong>Tasa</strong></p></th>
                      <th><p class="small"><strong>Oficial de Crédito</strong></p></th>
                      <th><p class="small"><strong>Ver detalle</strong></p></th>
                      <th><p class="small"><strong>Acciones</strong></p></th>
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
<!--<script>
  
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
      { data: "descripcion" },
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
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownDetalle${data}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Opciones
              </button>
              <div class="dropdown-menu" aria-labelledby="dropdownDetalle${data}">
                <a class="dropdown-item" href="consultar_solicitud.php?id_solicitud=${data}">Ver detalle</a>
                <a class="dropdown-item" href="../../pages/garantia/garantia.php?id_solicitud=${data}">Agregar garantía</a>
                <a class="dropdown-item" href="../../pages/obligaciones/obligaciones.php?id_solicitud=${data}">Agregar obligaciones</a>
              </div>
            </div>
          `;
        },
        orderable: false,
        searchable: false
      },
      {
        data: "cod_solicitud",
        render: function(data, type, row) {
          const isDisabled = row.id_prestamo === null;
          return `
            <a href="abono.php?id_solicitud=${data}" 
               class="btn btn-sm btn-success ${isDisabled ? 'disabled' : ''}" 
               data-toggle="tooltip" 
               data-placement="top" 
               title="${isDisabled ? 'Primero debe aprobar el préstamo' : 'Aplicar Pago'}"
               ${isDisabled ? 'onclick="return false;"' : ''}>
              <i class="fas fa-money-bill-wave"></i>
            </a>
          `;
        },
        orderable: false,
        searchable: false
      }
    ]
  });


});
</script>-->
<script>
let tabla;

function cargarTabla(filtros = {}) {
  // Destruir DataTable si ya está inicializado
  if ($.fn.DataTable.isDataTable('#clientesTable')) {
    $('#clientesTable').DataTable().destroy();
  }

  tabla = $('#clientesTable').DataTable({
    ajax: {
      url: 'fnprestamos.php',
      data: filtros,
      dataSrc: '',
      error: function (xhr, error, thrown) {
        console.log("Error en la carga de datos: ", error);
        console.log("Estado: ", xhr.status);
        console.log("Respuesta: ", xhr.responseText);
      }
    },
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'pdfHtml5',
        orientation: 'landscape',
        pageSize: 'LEGAL',
        title: 'Reporte de Créditos',
        exportOptions: {
          columns: ':visible:not(:last-child)' 
        }
      },
      'excelHtml5', 'csvHtml5', 'print'
    ],
    columns: [
      { data: "cod_solicitud" },
      { data: "descripcion" },
      { data: "nombre" },
      { data: "fecha_solicitud" },
      { data: "monto_solicitado" },
      { data: "estatus" },
      { data: "plazo_solicitado" },
      { data: "tasa" },
      { data: "oficial_credito" },
      {
        data: "cod_solicitud",
        render: function(data) {
          return `
            <div class="dropdown">
              <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                Opciones
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="consultar_solicitud.php?id_solicitud=${data}">Ver detalle</a>
                <a class="dropdown-item" href="../../pages/garantia/garantia.php?id_solicitud=${data}">Agregar garantía</a>
                <a class="dropdown-item" href="../../pages/obligaciones/obligaciones.php?id_solicitud=${data}">Agregar obligaciones</a>
              </div>
            </div>`;
        },
        orderable: false,
        searchable: false
      },
      {
        data: "cod_solicitud",
        render: function(data, type, row) {
          const isDisabled = row.id_prestamo === null;
          return `
            <a href="abono.php?id_solicitud=${data}" 
               class="btn btn-sm btn-success ${isDisabled ? 'disabled' : ''}" 
               data-toggle="tooltip" 
               title="${isDisabled ? 'Primero debe aprobar el préstamo' : 'Aplicar Pago'}"
               ${isDisabled ? 'onclick="return false;"' : ''}>
              <i class="fas fa-money-bill-wave"></i>
            </a>`;
        },
        orderable: false,
        searchable: false
      }
    ]
  });
}

// Al cargar la página, mostrar todos los registros
$(document).ready(function () {
  cargarTabla();

  // Cuando se hace clic en el botón Buscar
  $('#btnBuscar').on('click', function () {
    const filtros = {
      id_solicitud: $('#filtro_id').val().trim(),
      fecha: $('#filtro_fecha').val(),
      cliente: $('#filtro_cliente').val().trim()
    };

    // Eliminar campos vacíos
    Object.keys(filtros).forEach(key => {
      if (!filtros[key]) delete filtros[key];
    });

    cargarTabla(filtros);
  });
});
</script>
</body>
</html>
