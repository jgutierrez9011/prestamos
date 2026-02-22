<?php
require_once '../usuarios/reg.php';
require_once '../usuarios/fnusuario.php'; 
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
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Reporte de Cobro Diario</title>

   <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../../plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <?php
  if (!empty($_SESSION["user"])) {
    $menuBuilder = new MenuBuilder($base_de_datos, $_SESSION["user"]);
    echo $menuBuilder->buildMenu();
  }
  ?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1>Reporte de Cobro Diario</h1></div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Consultar Cobros por Fecha</h3></div>
      <div class="card-body">
        <form id="formReporteCobro" class="mb-3">
          <div class="form-row align-items-end">
            <!-- Fecha Inicio -->
            <div class="col-md-3">
              <label for="fecha_inicio">Fecha Inicio:</label>
              <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
            </div>

            <!-- Fecha Fin -->
            <div class="col-md-3">
              <label for="fecha_fin">Fecha Fin:</label>
              <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
            </div>

            <?php if ($perfilUsuario === 'Administrador'): ?>
            <!-- Filtro de Cartera -->
            <div class="col-md-3">
              <label for="cartera_select">Cartera:</label>
              <select class="form-control" id="cartera_select" name="cartera_select">
                <?php echo fillcartera_usuario('N',$base_de_datos) ?>
              </select>
            </div>
            <?php endif; ?>

            <!-- Botones -->
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search mr-1"></i> Buscar</button>
            </div>
            <div class="col-md-1">
              <button type="button" id="btnLimpiar" class="btn btn-default btn-block"><i class="fas fa-broom mr-1"></i></button>
            </div>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="tablaReporte">
            <thead class="thead-dark">
              <tr>
                <th>Cartera</th>
                <th>Cod. Solicitud</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Direccion domicilio</th>
                <th>Direccion negocio</th>
                <th>Fecha desembolso</th>
                <th>Fecha vencimiento</th>
                <th>Fecha Abono</th>
                <th>Numero cuota</th>
                <th>Valor Cuota</th>
                <th>Saldo Mora</th>
                <th>Cuota + Mora</th>
                <th>Abonado</th>
                <th>Saldo</th>
              </tr>
            </thead>
            <tbody id="resultadoReporte"></tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php require_once '../../footer.php'; ?>

<!-- Scripts -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../dist/js/adminlte.min.js"></script>
<!-- DataTables & Buttons -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<!-- Updated moment.js to CDN (use local patched copy + SRI in production) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

<script>
  const perfilUsuario = '<?= $perfilUsuario ?>';

  function cargarReporte(fechaInicio, fechaFin, cartera = '') {
    let url = `rptcobrodiario_service.php?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    if (perfilUsuario === 'Administrador' && cartera) {
      url += `&cartera=${cartera}`;
    }

    fetch(url)
      .then(resp => resp.status === 204 ? [] : resp.json())
      .then(data => {

        if ($.fn.DataTable.isDataTable('#tablaReporte')) {
           $('#tablaReporte').DataTable().clear().destroy();
         }

        const tbody = document.getElementById('resultadoReporte');
        tbody.innerHTML = '';
        (data || []).forEach(row => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${row.cartera || '-'}</td>
            <td>${row.cod_solicitud || '-'}</td>
            <td>${row.cliente || '-'}</td>
            <td>${row.telefono || '-'}</td>
            <td>${row.direccion_domicilio || '-'}</td>
            <td>${row.direccion_negocio || '-'}</td>
            <td>${row.fecha_desembolso || '-'}</td>
            <td>${row.fecha_vence || '-'}</td>
            <td>${row.fecha_abono || '-'}</td>
            <td>${row.numero_cuota || '-'}</td>
            <td>${parseFloat(row.valor_cuota || 0).toFixed(2)}</td>
            <td>${parseFloat(row.saldo_mora || 0).toFixed(2)}</td>
            <td>${parseFloat(row.cuota_mas_mora || 0).toFixed(2)}</td>
            <td>${parseFloat(row.abonado || 0).toFixed(2)}</td>
            <td>${parseFloat(row.saldo || 0).toFixed(2)}</td>`;
          tbody.appendChild(tr);
        });

       $('#tablaReporte').DataTable({
  destroy: true,
  responsive: true,
  autoWidth: false,
  dom: 'Bfrtip',
  buttons: [
  'copy',
  'excel',
  {
    extend: 'pdfHtml5',
    text: 'PDF',
    orientation: 'landscape',
    pageSize: 'A4',
    exportOptions: {
      columns: ':visible'
    },
    customize: function (doc) {
      doc.content[0].alignment = 'center';
      const columnCount = doc.content[1].table.body[0].length;
      doc.content[1].table.widths = Array(columnCount).fill('*');
      doc.defaultStyle.fontSize = 7;
      doc.styles.tableHeader.alignment = 'center';
      doc.styles.tableBodyEven.alignment = 'center';
      doc.styles.tableBodyOdd.alignment = 'center';
    }
  },
  'print'
]

});


      })
      .catch(err => {
        console.error('Error al consultar:', err);
        document.getElementById('resultadoReporte').innerHTML = '<tr><td colspan="10">Error al obtener datos</td></tr>';
      });
  }

  document.getElementById('formReporteCobro').addEventListener('submit', function(e) {
    e.preventDefault();

    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const cartera = document.getElementById('cartera_select')?.value || '';

    if (!fechaInicio || !fechaFin) {
      alert('Debe seleccionar ambas fechas');
      return;
    }

    cargarReporte(fechaInicio, fechaFin, cartera);
  });

  document.getElementById('btnLimpiar').addEventListener('click', function() {
    document.getElementById('fecha_inicio').value = '';
    document.getElementById('fecha_fin').value = '';
    if (document.getElementById('cartera_select')) {
      document.getElementById('cartera_select').value = '';
    }
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_inicio').value = hoy;
    document.getElementById('fecha_fin').value = hoy;
    cargarReporte(hoy, hoy);
  });

  document.addEventListener('DOMContentLoaded', () => {
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_inicio').value = hoy;
    document.getElementById('fecha_fin').value = hoy;
    cargarReporte(hoy, hoy);
  });
</script>
</body>
</html>
