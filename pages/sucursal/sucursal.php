<?php
require_once '../usuarios/reg.php'; // Para sesión
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Listado de Sucursales</title>
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
        <h1>Listado de Sucursales</h1>
        <button class="btn btn-success" data-toggle="modal" data-target="#modalAgregar">Agregar Sucursal</button>
      </div>
    </section>

    <section class="content">
      <div class="card">
        <div class="card-body">
          <table id="tablaSucursales" class="display">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Fecha de Apertura</th>
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
          <h5 class="modal-title">Editar Sucursal</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="sucursal_id" id="editId">
          <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" id="editNombre" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Dirección</label>
            <input type="text" name="direccion" id="editDireccion" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" id="editTelefono" class="form-control">
          </div>
          <div class="form-group">
            <label>Fecha de Apertura</label>
            <input type="date" name="fecha_apertura" id="editFecha" class="form-control" required>
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
          <h5 class="modal-title">Agregar Nueva Sucursal</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Dirección</label>
            <input type="text" name="direccion" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control">
          </div>
          <div class="form-group">
            <label>Fecha de Apertura</label>
            <input type="date" name="fecha_apertura" class="form-control" required>
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
    const tabla = $('#tablaSucursales').DataTable({
      ajax: {
        url: 'servicio_sucursal.php',
        type: 'POST',
        data: { accion: 'listar' },
        dataSrc: ''
      },
      columns: [
        { data: 'sucursal_id' },
        { data: 'nombre' },
        { data: 'direccion' },
        { data: 'telefono' },
        { data: 'fecha_apertura' },
        {
          data: null,
          render: function (data, type, row) {
            return `<button class="btn btn-sm btn-primary btn-editar" 
                      data-id="${row.sucursal_id}" 
                      data-nombre="${row.nombre}" 
                      data-direccion="${row.direccion}" 
                      data-telefono="${row.telefono}" 
                      data-fecha="${row.fecha_apertura.substr(0,10)}">Editar</button>`;
          }
        }
      ]
    });

    $(document).on('click', '.btn-editar', function () {
      $('#editId').val($(this).data('id'));
      $('#editNombre').val($(this).data('nombre'));
      $('#editDireccion').val($(this).data('direccion'));
      $('#editTelefono').val($(this).data('telefono'));
      $('#editFecha').val($(this).data('fecha'));
      $('#modalEditar').modal('show');
    });

    $('#formEditar').on('submit', function (e) {
      e.preventDefault();
      const datos = $(this).serializeArray();
      datos.push({ name: 'accion', value: 'editar' });
      $.post('servicio_sucursal.php', datos, function (resp) {
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
      $.post('servicio_sucursal.php', datos, function (resp) {
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
