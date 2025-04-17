<?php
require_once '../usuarios/reg.php'; // Para sesión
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Listado de Carteras</title>
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
        <h1>Listado de Carteras</h1>
        <button class="btn btn-success" data-toggle="modal" data-target="#modalAgregar">Agregar Cartera</button>
      </div>
    </section>

    <section class="content">
      <div class="card">
        <div class="card-body">
          <table id="tablaCarteras" class="display">
            <thead>
              <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Monto Mínimo</th>
                <th>Monto Máximo</th>
                <th>Estado</th>
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

<!-- Modal de Edición -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form id="formEditar">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Cartera</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idcartera" id="editId">
          <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" id="editDescripcion" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Monto Mínimo</label>
            <input type="number" name="monto_minimo" id="editMinimo" class="form-control" required step="0.01">
          </div>
          <div class="form-group">
            <label>Monto Máximo</label>
            <input type="number" name="monto_maximo" id="editMaximo" class="form-control" required step="0.01">
          </div>
          <div class="form-group">
            <label>Estado</label>
            <select name="estado" id="editEstado" class="form-control">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
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

<!-- Modal de Agregar -->
<div class="modal fade" id="modalAgregar" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form id="formAgregar">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Nueva Cartera</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Monto Mínimo</label>
            <input type="number" name="monto_minimo" class="form-control" required step="0.01">
          </div>
          <div class="form-group">
            <label>Monto Máximo</label>
            <input type="number" name="monto_maximo" class="form-control" required step="0.01">
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

<!-- JS -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function () {
    const tabla = $('#tablaCarteras').DataTable({
      ajax: {
        url: 'servicio_cartera.php',
        type: 'POST',
        data: { accion: 'listar' },
        dataSrc: ''
      },
      columns: [
        { data: 'idcartera' },
        { data: 'descripcion' },
        { data: 'monto_minimo' },
        { data: 'monto_maximo' },
        {
          data: 'estado',
          render: function (data) {
            return data == 1 ? 'Activo' : 'Inactivo';
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            return `<button class="btn btn-sm btn-primary btn-editar" 
                      data-id="${row.idcartera}" 
                      data-descripcion="${row.descripcion}" 
                      data-min="${row.monto_minimo}" 
                      data-max="${row.monto_maximo}" 
                      data-estado="${row.estado}">Editar</button>`;
          }
        }
      ]
    });

    $(document).on('click', '.btn-editar', function () {
      $('#editId').val($(this).data('id'));
      $('#editDescripcion').val($(this).data('descripcion'));
      $('#editMinimo').val($(this).data('min'));
      $('#editMaximo').val($(this).data('max'));
      $('#editEstado').val($(this).data('estado'));
      $('#modalEditar').modal('show');
    });

    $('#formEditar').on('submit', function (e) {
      e.preventDefault();
      const datos = $(this).serializeArray();
      datos.push({ name: 'accion', value: 'editar' });
      $.post('servicio_cartera.php', datos, function (resp) {
        if (resp.success) {
          $('#modalEditar').modal('hide');
          tabla.ajax.reload();
        } else {
          alert('Error al actualizar');
        }
      }, 'json');
    });

    $('#formAgregar').on('submit', function (e) {
      e.preventDefault();
      const datos = $(this).serializeArray();
      datos.push({ name: 'accion', value: 'insertar' });
      $.post('servicio_cartera.php', datos, function (resp) {
        if (resp.success) {
          $('#modalAgregar').modal('hide');
          $('#formAgregar')[0].reset();
          tabla.ajax.reload();
        } else {
          alert('Error al agregar');
        }
      }, 'json');
    });
  });
</script>
</body>
</html>
