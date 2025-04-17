<?php
require_once '../usuarios/reg.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Listado de Garantías</title>
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
        <h1>Listado de Garantías</h1>
        <button class="btn btn-success" data-toggle="modal" data-target="#modalEditar">Agregar Garantía</button>
      </div>
    </section>

    <section class="content">
      <div class="card">
        <div class="card-body">
          <table id="tablaGarantias" class="display">
            <thead>
              <tr>
                <th>ID</th>
                <th>ID Solicitud</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Marca</th>
                <th>Color</th>
                <th>Ubicación</th>
                <th>Valor Realización</th>
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

<!-- Modal Editar/Agregar Garantía -->
<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form id="formEditar">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Garantía</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_garantia" id="editId">
          <div class="form-group">
            <label>ID Solicitud</label>
            <input type="number" name="id_solicitud" id="editSolicitud" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" id="editDescripcion" class="form-control" required></textarea>
          </div>
          <div class="form-group">
            <label>Cantidad</label>
            <input type="number" name="cantidad" id="editCantidad" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Marca</label>
            <input type="text" name="marca" id="editMarca" class="form-control">
          </div>
          <div class="form-group">
            <label>Color</label>
            <input type="text" name="color" id="editColor" class="form-control">
          </div>
          <div class="form-group">
            <label>Ubicación</label>
            <textarea name="ubicacion" id="editUbicacion" class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label>Valor Realización</label>
            <input type="number" step="0.01" name="valor_realizacion" id="editValor" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Eliminar Garantía -->
<div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <form id="formEliminar">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Garantía</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <p id="mensajeEliminar">¿Está seguro que desea eliminar esta garantía?</p>
          <input type="hidden" name="id_garantia" id="eliminarId">
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
    const tabla = $('#tablaGarantias').DataTable({
      ajax: {
        url: 'servicio_garantia.php',
        type: 'POST',
        data: { accion: 'listar' },
        dataSrc: ''
      },
      columns: [
        { data: 'id_garantia' },
        { data: 'id_solicitud' },
        { data: 'descripcion' },
        { data: 'cantidad' },
        { data: 'marca' },
        { data: 'color' },
        { data: 'ubicacion' },
        { data: 'valor_realizacion' },
        {
          data: null,
          render: function (data, type, row) {
            return `
              <button class="btn btn-sm btn-primary btn-editar"
                data-id="${row.id_garantia}"
                data-solicitud="${row.id_solicitud}"
                data-descripcion="${row.descripcion}"
                data-cantidad="${row.cantidad}"
                data-marca="${row.marca}"
                data-color="${row.color}"
                data-ubicacion="${row.ubicacion}"
                data-valor="${row.valor_realizacion}">Editar</button>
              <button class="btn btn-sm btn-danger btn-eliminar" data-id="${row.id_garantia}" data-descripcion="${row.descripcion}">Eliminar</button>`;
          }
        }
      ]
    });

    $(document).on('click', '.btn-editar', function () {
      $('#editId').val($(this).data('id'));
      $('#editSolicitud').val($(this).data('solicitud'));
      $('#editDescripcion').val($(this).data('descripcion'));
      $('#editCantidad').val($(this).data('cantidad'));
      $('#editMarca').val($(this).data('marca'));
      $('#editColor').val($(this).data('color'));
      $('#editUbicacion').val($(this).data('ubicacion'));
      $('#editValor').val($(this).data('valor'));
      $('#modalEditar .modal-title').text('Editar Garantía');
      $('#modalEditar').modal('show');
    });

    $('.btn-success').on('click', function () {
      $('#formEditar')[0].reset();
      $('#editId').val('');
      $('#modalEditar .modal-title').text('Agregar Garantía');
    });

    $('#formEditar').on('submit', function (e) {
      e.preventDefault();
      const datos = $(this).serializeArray();
      const accion = $('#editId').val() ? 'editar' : 'insertar';
      datos.push({ name: 'accion', value: accion });
      $.post('servicio_garantia.php', datos, function (resp) {
        if (resp.success) {
          $('#modalEditar').modal('hide');
          tabla.ajax.reload();
          $('#formEditar')[0].reset();
        } else {
          alert('Error al guardar los datos');
        }
      }, 'json');
    });

    $(document).on('click', '.btn-eliminar', function () {
      const id = $(this).data('id');
      const descripcion = $(this).data('descripcion');
      $('#eliminarId').val(id);
      $('#mensajeEliminar').text(`¿Está seguro que desea eliminar la garantía: "${descripcion}"?`);
      $('#modalEliminar').modal('show');
    });

    $('#formEliminar').on('submit', function (e) {
      e.preventDefault();
      const datos = $(this).serializeArray();
      datos.push({ name: 'accion', value: 'eliminar' });
      $.post('servicio_garantia.php', datos, function (resp) {
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
