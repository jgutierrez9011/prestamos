<?php
require_once  '../usuarios/reg.php'; 
require_once '../../menu_builder.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Reporte Interés por Cartera</title>

  <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Google Font -->
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
            <h1>Reporte de movimientos por Cartera</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Inicio</a></li>
              <li class="breadcrumb-item active">movimientos por Cartera</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Consultar Interés Pendiente</h3>
        </div>
        <div class="card-body">

          <form id="formReporteInteres" class="form-inline mb-3">
            <div class="form-group mr-2">
              <label for="fechainicio" class="mr-2">Desde:</label>
              <input type="date" class="form-control" id="fechainicio" name="fechainicio">
            </div>
            <div class="form-group mr-2">
              <label for="fechafin" class="mr-2">Hasta:</label>
              <input type="date" class="form-control" id="fechafin" name="fechafin">
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tablaReporte">
              <thead class="thead-dark">
                <tr>
                  <th>Cartera</th>
                  <th>Código</th>
                  <th>ID Préstamo</th>
                  <th>Monto Total</th>
                  <th>Monto Abonado</th>
                  <th>Saldo Total</th>
                  <th>Interés Pendiente</th>
                </tr>
              </thead>
              <tbody id="resultadoReporte">
              </tbody>
            </table>
          </div>

        </div>
        <div class="card-footer">
          Total de registros: <span id="totalRegistros">0</span>
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

function cargarReporte(fechainicio = '', fechafin = '') {
  let url = 'rptmovcartera_service.php';

  const params = new URLSearchParams();
  if (fechainicio) params.append('fechainicio', fechainicio);
  if (fechafin) params.append('fechafin', fechafin);
  if (params.toString()) {
    url += '?' + params.toString();
  }

  fetch(url)
    .then(response => response.json())
    .then(data => {
      console.log("Datos recibidos:", data); // Para depurar

      if (tabla) {
        tabla.destroy();
      }

      const tbody = document.getElementById('resultadoReporte');
      tbody.innerHTML = '';
      let total = 0;

      const registros = Array.isArray(data) ? data : [];

      if (registros.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center">No se encontraron resultados para los filtros aplicados.</td></tr>`;
        document.getElementById('totalRegistros').textContent = 0;
        return;
      }

      registros.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${row.descripcion}</td>
          <td>${row.cod_solicitud}</td>
          <td>${row.id_prestamo}</td>
          <td>${row.montotal}</td>
          <td>${row.monto_abonado}</td>
          <td>${row.saldo_total_cartera}</td>
          <td>${row.interes_pendiente}</td>
        `;
        tbody.appendChild(tr);
        total++;
      });

      document.getElementById('totalRegistros').textContent = total;

      tabla = $('#tablaReporte').DataTable({
        responsive: true,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: [
          'copy',
          'excel',
          {
            extend: 'pdfHtml5',
            orientation: 'landscape',
            pageSize: 'A4',
            title: 'CREDIMORE - Interés por Cartera',
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
        language: {
          url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
        }
      });
    })
    .catch(error => {
      console.error('Error al cargar el reporte:', error);
      document.getElementById('resultadoReporte').innerHTML = `<tr><td colspan="7">Error al obtener los datos</td></tr>`;
      document.getElementById('totalRegistros').textContent = 0;
    });
}

document.addEventListener('DOMContentLoaded', () => {
  cargarReporte();
});

document.getElementById('formReporteInteres').addEventListener('submit', function (e) {
  e.preventDefault();
  const fechainicio = document.getElementById('fechainicio').value;
  const fechafin = document.getElementById('fechafin').value;
  cargarReporte(fechainicio, fechafin);
});
</script>

</body>
</html>