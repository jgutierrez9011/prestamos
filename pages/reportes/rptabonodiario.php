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
  <title><?php require_once '../../titulo.php'; ?> | Reporte de Abonos</title>

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
          <div class="col-sm-6">
            <h1>Reporte de Abonos</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Inicio</a></li>
              <li class="breadcrumb-item active">Reporte de Abonos</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Consultar Abonos por Fecha</h3>
        </div>
        <div class="card-body">

          <form id="formReporteAbonos" class="mb-3">
    <div class="form-row align-items-end">
      <!-- Fecha Inicio -->
      <div class="col-md-3">
        <label for="fecha_inicio">Fecha Inicio:</label>
        <div class="input-group date" id="fecha_inicio" data-target-input="nearest">
          <input type="text" class="form-control datetimepicker-input" data-target="#fecha_inicio" required/>
          <div class="input-group-append" data-target="#fecha_inicio" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
          </div>
        </div>
      </div>
      
      <!-- Fecha Fin -->
      <div class="col-md-3">
        <label for="fecha_fin">Fecha Fin:</label>
        <div class="input-group date" id="fecha_fin" data-target-input="nearest">
          <input type="text" class="form-control datetimepicker-input" data-target="#fecha_fin" required/>
          <div class="input-group-append" data-target="#fecha_fin" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
          </div>
        </div>
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
        <button type="submit" class="btn btn-primary btn-block">
          <i class="fas fa-search mr-1"></i> Buscar
        </button>
      </div>
      <div class="col-md-2">
        <button type="button" id="btnLimpiar" class="btn btn-default btn-block">
          <i class="fas fa-broom mr-1"></i> Limpiar
        </button>
      </div>
    </div>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tablaReporte">
              <thead class="thead-dark">
                <tr>
                  <th>Cartera</th>
                  <th>Código Préstamo</th>
                  <th>Cliente</th>
                  <th>Teléfono</th>
                  <th>Fecha Abono</th>
                  <th>Monto Abonado C$</th>
                  <th>Usuario Registró</th>
                </tr>
              </thead>
              <tbody id="resultadoReporte">
              </tbody>
            </table>
          </div>

        </div>
        <div class="card-footer">
          Total de resultados: <span id="totalRegistros">0</span>
          <span class="float-right">Total abonado: <span id="totalAbonado">$0.00</span></span>
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
<!-- Tempusdominus Bootstrap 4 -->
<!-- Updated moment.js to CDN (use local patched copy + SRI in production) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

<script>
  let tabla;
  const perfilUsuario = '<?= $perfilUsuario ?>';

  // Inicializar datepickers
  $(function() {
    $('#fecha_inicio').datetimepicker({
      format: 'YYYY-MM-DD',
      locale: 'es'
    });
    
    $('#fecha_fin').datetimepicker({
      format: 'YYYY-MM-DD',
      locale: 'es',
      useCurrent: false
    });
    
    // Validar que fecha fin sea mayor o igual a fecha inicio
    $('#fecha_inicio').on('change.datetimepicker', function(e) {
      $('#fecha_fin').datetimepicker('minDate', e.date);
    });
    
    $('#fecha_fin').on('change.datetimepicker', function(e) {
      $('#fecha_inicio').datetimepicker('maxDate', e.date);
    });
  });

  function cargarReporte(fechaInicio = '', fechaFin = '', cartera = '') {
    // Validar que ambas fechas estén presentes
    /*if ((fechaInicio && !fechaFin) || (!fechaInicio && fechaFin)) {
      alert('Debe seleccionar ambas fechas para filtrar');
      return;
    }*/

    let url = 'rptabono_service.php';
    //if (fechaInicio && fechaFin) {
      url += `?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    //}
    if (perfilUsuario === 'Administrador' && cartera) {
      url += `&cartera=${cartera}`;
    }

    fetch(url)
      .then(response => {
        if (response.status === 204) {
          return [];
        }
        return response.json();
      })
      .then(data => {
        if (tabla) {
          tabla.destroy();
        }

        const tbody = document.getElementById('resultadoReporte');
        tbody.innerHTML = '';
        let total = 0;
        let totalAbonado = 0;

        const registros = Array.isArray(data) ? data : [];

        registros.forEach(row => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${row.cartera || '-'}</td>
            <td>${row.cod_solicitud || '-'}</td>
            <td>${row.cliente || '-'}</td>
            <td>${row.telefono || '-'}</td>
            <td>${row.fecha_creo || '-'}</td>
            <td>${parseFloat(row.monto_abonado || 0).toFixed(2)}</td>
            <td>${row.usuario_creo || '-'}</td>
          `;
          tbody.appendChild(tr);
          total++;
          totalAbonado += parseFloat(row.monto_abonado || 0);
        });

        document.getElementById('totalRegistros').textContent = total;
        document.getElementById('totalAbonado').textContent = `$${totalAbonado.toFixed(2)}`;

        tabla = $('#tablaReporte').DataTable({
          responsive: true,
          autoWidth: false,
          destroy: true,
          dom: 'Bfrtip',
          buttons: [
            'copy',
            {
              extend: 'excel',
              title: 'CREDIMORE - Reporte de Abonos diario',
              messageTop: function() {
                let msg = 'Reporte de Abonos\n';
                if (fechaInicio && fechaFin) {
                  msg += `Desde: ${fechaInicio} Hasta: ${fechaFin}\n`;
                }
                msg += `Total abonado: C$${totalAbonado.toFixed(2)}`;
                return msg;
              }
            },
            {
              extend: 'pdfHtml5',
              orientation: 'landscape',
              pageSize: 'A4',
              title: 'Reporte de Abonos',
              messageTop: function() {
                let msg = 'Reporte de Abonos\n';
                if (fechaInicio && fechaFin) {
                  msg += `Desde: ${fechaInicio} Hasta: ${fechaFin}\n`;
                }
                msg += `Total abonado: C$${totalAbonado.toFixed(2)}`;
                return msg;
              },
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
            {
              extend: 'print',
              title: 'Reporte de Abonos',
              messageTop: function() {
                let msg = '<h3>Reporte de Abonos</h3>';
                if (fechaInicio && fechaFin) {
                  msg += `<p><strong>Período:</strong> ${fechaInicio} al ${fechaFin}</p>`;
                }
                msg += `<p><strong>Total abonado:</strong> $${totalAbonado.toFixed(2)}</p>`;
                return msg;
              }
            }
          ],
          language: {
            url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
          },
          order: [[4, 'desc']] // Ordenar por fecha descendente por defecto
        });
      })
      .catch(error => {
        console.error('Error al cargar el reporte:', error);
        document.getElementById('resultadoReporte').innerHTML = '<tr><td colspan="7">Error al obtener los datos</td></tr>';
        document.getElementById('totalRegistros').textContent = '0';
        document.getElementById('totalAbonado').textContent = '$0.00';
      });
  }

  // Evento al cargar la página
  document.addEventListener('DOMContentLoaded', () => {
    cargarReporte(); // Carga todos los registros inicialmente
  });

  // Evento del formulario
  document.getElementById('formReporteAbonos').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fechaInicio = $('#fecha_inicio').datetimepicker('viewDate').format('YYYY-MM-DD');
    const fechaFin = $('#fecha_fin').datetimepicker('viewDate').format('YYYY-MM-DD');
    const cartera = document.getElementById('cartera_select')?.value || '';
    
    // Validar que ambas fechas estén seleccionadas
    if (!fechaInicio || !fechaFin) {
      alert('Debe seleccionar ambas fechas para filtrar');
      return;
    }
    
    cargarReporte(fechaInicio, fechaFin, cartera);
  });

  // Evento del botón limpiar
  document.getElementById('btnLimpiar').addEventListener('click', function() {
    $('#fecha_inicio').datetimepicker('clear');
    $('#fecha_fin').datetimepicker('clear');
    cargarReporte(); // Recargar sin filtros
  });
</script>
</body>
</html>