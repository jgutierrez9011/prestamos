<?php
require_once '../usuarios/reg.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Listado de Obligaciones Financieras</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <?php require_once '../../menu.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1>Listado de Obligaciones Financieras</h1>
        <button class="btn btn-success" data-toggle="modal" data-target="#modalAgregar">Agregar Obligación</button>
      </div>
    </section>

    <section class="content">
      <div class="card">
        <div class="card-body">
          <table id="tablaObligaciones" class="display">
            <thead>
              <tr>
                <th>ID</th>
                <th>ID Solicitud</th>
                <th>Institución</th>
                <th>Monto Inicial</th>
                <th>Saldo</th>
                <th>Cuota</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

  <?php require_once '../../footer.php'; ?>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form id="formAgregar">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Obligación Financiera</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>ID Solicitud</label>
            <input type="number" name="id_solicitud" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Institución</label>
            <input type="text" name="institucion" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Monto Inicial</label>
            <input type="number" step="0.01" name="monto_inicial" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Saldo</label>
            <input type="number" step="0.01" name="saldo" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Cuota</label>
            <input type="number" step="0.01" name="cuota" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Agregar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form id="formEditar">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Obligación Financiera</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_obligacion" id="editId">
          <div class="form-group">
            <label>ID Solicitud</label>
            <input type="number" name="id_solicitud" id="editSolicitud" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Institución</label>
            <input type="text" name="institucion" id="editInstitucion" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Monto Inicial</label>
            <input type="number" step="0.01" name="monto_inicial" id="editMontoInicial" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Saldo</label>
            <input type="number" step="0.01" name="saldo" id="editSaldo" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Cuota</label>
            <input type="number" step="0.01" name="cuota" id="editCuota" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar cambios</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form id="formEliminar">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Obligación</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <p>¿Está seguro que desea eliminar esta obligación financiera?</p>
          <input type="hidden" name="id_obligacion" id="eliminarId">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Eliminar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- JS -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function () {
    const tabla = $('#tablaObligaciones').DataTable({
      ajax: {
        url: 'servicio_obligaciones.php',
        type: 'POST',
        data: { accion: 'listar' },
        dataSrc: ''
      },
      columns: [
        { data: 'id_obligacion' },
        { data: 'id_solicitud' },
        { data: 'institucion' },
        { data: 'monto_inicial' },
        { data: 'saldo' },
        { data: 'cuota' },
        {
          data: null,
          render: function (data, type, row) {
            return `
              <button class="btn btn-sm btn-primary btn-editar"
                data-id="${row.id_obligacion}"
                data-solicitud="${row.id_solicitud}"
                data-institucion="${row.institucion}"
                data-monto="${row.monto_inicial}"
                data-saldo="${row.saldo}"
                data-cuota="${row.cuota}">Editar</button>
              <button class="btn btn-sm btn-danger btn-eliminar" data-id="${row.id_obligacion}">Eliminar</button>`;
          }
        }
      ]
    });

    $(document).on('click', '.btn-editar', function () {
      $('#editId').val($(this).data('id'));
      $('#editSolicitud').val($(this).data('solicitud'));
      $('#editInstitucion').val($(this).data('institucion'));
      $('#editMontoInicial').val($(this).data('monto'));
      $('#editSaldo').val($(this).data('saldo'));
      $('#editCuota').val($(this).data('cuota'));
      $('#modalEditar').modal('show');
    });

    $(document).on('click', '.btn-eliminar', function () {
      $('#eliminarId').val($(this).data('id'));
      $('#modalEliminar').modal('show');
    });

    $('#formAgregar').on('submit', function (e) {
      e.preventDefault();
      const datos = $(this).serializeArray();
      datos.push({ name: 'accion', value: 'insertar' });
      $.post('servicio_obligaciones.php', datos, function (resp) {
        if (resp.success) {
          $('#modalAgregar').modal('hide');
          $('#formAgregar')[0].reset();
          tabla.ajax.reload();
        } else {
          alert('Error al agregar');
        }
      }, 'json');
    });

    $('#formEditar').on('submit', function (e) {
      e.preventDefault();
      const datos = $(this).serializeArray();
      datos.push({ name: 'accion', value: 'editar' });
      $.post('servicio_obligaciones.php', datos, function (resp) {
        if (resp.success) {
          $('#modalEditar').modal('hide');
          tabla.ajax.reload();
        } else {
          alert('Error al actualizar');
        }
      }, 'json');
    });

    $('#formEliminar').on('submit', function (e) {
      e.preventDefault();
      const datos = $(this).serializeArray();
      datos.push({ name: 'accion', value: 'eliminar' });
      $.post('servicio_obligaciones.php', datos, function (resp) {
        if (resp.success) {
          $('#modalEliminar').modal('hide');
          tabla.ajax.reload();
        } else {
          alert('Error al eliminar');
        }
      }, 'json');
    });
  });
</script>
</body>
</html>
