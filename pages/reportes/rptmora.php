<?php
require_once  '../usuarios/reg.php';
require_once '../usuarios/fnusuario.php'; 
require_once '../../menu_builder.php';
$perfilUsuario = $_SESSION['perfilusuario'] ?? 'Usuario';
$codigoCartera = $_SESSION['carterausuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Reporte de Mora</title>

  <!-- DataTables -->
<link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
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
          <div class="col-sm-6">
            <h1>Reporte de Mora</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Inicio</a></li>
              <li class="breadcrumb-item active">Reporte de Mora</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Consultar Mora por Préstamo</h3>
        </div>
        <div class="card-body">

          <form id="formReporteMora" class="form-inline mb-3">
            <div class="form-group mr-2">
              <label for="id_prestamo" class="mr-2">Codigo de préstamo:</label>
              <input type="number" class="form-control" id="id_prestamo" name="id_prestamo" placeholder="Ej: 84">
            </div>

            <?php if ($perfilUsuario === 'Administrador'): ?>
            <div class="form-group mr-2">
             <label for="codigoCarterafiltro" class="mr-2">Cartera:</label>
              <select class="form-control" id="codigoCarterafiltro" name="codigoCarterafiltro">
                <?php echo fillcartera_usuario('N',$base_de_datos) ?>
              </select>
            </div>
             <?php endif; ?>



            <button type="submit" class="btn btn-primary">Buscar</button>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tablaReporte">
              <thead class="thead-dark">
                <tr>
                  <th>Cartera</th>
                  <th>Código</th>
                  <th>Estatus</th>
                  <th>Cliente</th>
                  <th>Teléfono</th>
                  <th>Dirección Domicilio</th>
                  <th>Dirección Negocio</th>
                  <th>Días Mora</th>
                  <th>Días Promedio</th>
                  <th>Cuotas Vencidas</th>
                  <th>Saldo Mora</th>
                  <th>Saldo Pendiente</th>
                  <th>Vencimiento</th>
                </tr>
              </thead>
              <tbody id="resultadoReporte">
              </tbody>
            </table>
          </div>

        </div>
        <div class="card-footer">
          Total de resultados: <span id="totalRegistros">0</span>
        </div>
      </div>
    </section>
  </div>

  <?php require_once '../../footer.php'; ?>

  <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

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

<script>
  let tabla;

function cargarReporte(id_prestamo = '', codigoCarterafiltro = '') {
  let url = 'rptmora_service.php';
  const params = new URLSearchParams();

  if (id_prestamo) params.append('id_prestamo', id_prestamo);
  if (codigoCarterafiltro) params.append('codigoCarterafiltro', codigoCarterafiltro);

  if ([...params].length > 0) url += '?' + params.toString();

  fetch(url)
    .then(response => response.json())
    .then(data => {
      if (tabla) {
        tabla.destroy();
      }

      const tbody = document.getElementById('resultadoReporte');
      tbody.innerHTML = '';
      let total = 0;
      const registros = Array.isArray(data) ? data : [];

      registros.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${row.cartera}</td>
          <td>${row.cod_solicitud}</td>
          <td>${row.estatus}</td>
          <td>${row.cliente}</td>
          <td>${row.telefono}</td>
          <td>${row.direccion_domicilio}</td>
          <td>${row.direccion_negocio}</td>
          <td>${row.dias_mora}</td>
          <td>${row.dias_promedio}</td>
          <td>${row.cuotas_vencidas}</td>
          <td>${row.saldo_mora}</td>
          <td>${row.saldo}</td>
          <td>${row.vencimiento_prestamo || "-"}</td>
        `;
        tbody.appendChild(tr);
        total++;
      });

      document.getElementById('totalRegistros').textContent = total;

      tabla = $('#tablaReporte').DataTable({
        responsive: true,
        autoWidth: false,
        destroy: true,
        dom: 'Bfrtip',
        buttons: [
          'copy', 'excel',
          {
            extend: 'pdfHtml5',
            orientation: 'landscape',
            pageSize: 'A4',
            title: 'CREDIMORE - Reporte de Mora',
            customize: function (doc) {
              doc.styles.title = {
                alignment: 'center',
                fontSize: 14,
                bold: true,
              };
              doc.defaultStyle.fontSize = 9;
              doc.styles.tableHeader.fontSize = 10;
            }
          },
          'print'
        ],

      });
    })
    .catch(error => {
      console.error('Error al cargar el reporte:', error);
      document.getElementById('resultadoReporte').innerHTML = `<tr><td colspan="12">Error al obtener los datos</td></tr>`;
      document.getElementById('totalRegistros').textContent = 0;
    });
}


  // Evento al cargar la página
  document.addEventListener('DOMContentLoaded', () => {
    cargarReporte(); // carga todos los registros
  });

  // Evento del formulario
document.getElementById('formReporteMora').addEventListener('submit', e => {
  e.preventDefault();

  const toNullInt = val => val.trim() === '' ? null : parseInt(val, 10);

  const id_prestamo = toNullInt(document.getElementById('id_prestamo').value);

  // Verifica si el campo de cartera existe (solo para Administrador)
  const carteraSelect = document.getElementById('codigoCarterafiltro');
  const codigoCarterafiltro = carteraSelect ? toNullInt(carteraSelect.value) : null;

  cargarReporte(id_prestamo, codigoCarterafiltro);
});


</script>
</body>
</html>
