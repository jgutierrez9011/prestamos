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
   <!-- SweetAlert2 -->
   <link rel="stylesheet" href="../../plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">

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
      
       <!-- Sección para mostrar la información del cliente -->
       <div id="client-info" class="card collapsed-card">
            <div class="card-header">
              <h3 class="card-title">Información del Cliente</h3>
              <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
            </div>
            <div class="card-body">
              <div class="form-row">
                 <div class="form-group col-md-4">
                    <label>Solicitud</label>
                    <input type="text" class="form-control form-control-sm" name="cod_solicitud" id="cod_solicitud" readonly>
                 </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="cedula">Cédula</label>
                  <input type="text" class="form-control form-control-sm" id="cedula" name="cedula" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="nombre">Nombre</label>
                  <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="telefono">Teléfono</label>
                  <input type="text" class="form-control form-control-sm" id="telefono" name="telefono" readonly>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="estado_civil">Estado Civil</label>
                  <input type="text" class="form-control form-control-sm" id="estado_civil" name="estado_civil" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="tipo_vivienda">Tipo de Vivienda</label>
                  <input type="text" class="form-control form-control-sm" id="tipo_vivienda" name="tipo_vivienda" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="anos_habitar">Años de Habitar</label>
                  <input type="text" class="form-control form-control-sm" id="anos_habitar" name="anos_habitar" readonly>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-12">
                  <label for="direccion_domicilio">Dirección del Domicilio</label>
                  <input type="text" class="form-control form-control-sm" id="direccion_domicilio" name="direccion_domicilio" readonly>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="actividad_economica">Actividad Económica</label>
                  <input type="text" class="form-control form-control-sm" id="actividad_economica" name="actividad_economica" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="rubro">Rubro</label>
                  <input type="text" class="form-control form-control-sm" id="rubro" name="rubro" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="tipo_local">Tipo de Local</label>
                  <input type="text" class="form-control form-control-sm" id="tipo_local" name="tipo_local" readonly>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="tiempo_operar">Tiempo de Operar</label>
                  <input type="text" class="form-control form-control-sm" id="tiempo_operar" name="tiempo_operar" readonly>
                </div>
                <div class="form-group col-md-8">
                  <label for="direccion_negocio">Dirección del Negocio</label>
                  <input type="text" class="form-control form-control-sm" id="direccion_negocio" name="direccion_negocio" readonly>
                </div>
              </div>
            </div>
          </div>

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
            <!--<label>ID Solicitud</label>-->
            <input type="hidden" name="id_solicitud" id="editSolicitud" class="form-control" required>
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
<!-- SweetAlert2 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>

<script src="../../dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function () {
 
    // Obtener el parámetro de la URL (id o cédula)
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id_solicitud'); // Obtener el valor del parámetro 'id'
    const cod=0;

    // Realizar la solicitud AJAX
    $.ajax({
        url: `../../pages/prestamos/fnprestamos.php?id_solicitud=${id}`, // Enviar el ID como parámetro GET
        method: 'GET',
        success: function(response) {
          const data = response[0]; // extrae el primer objeto del array
          // Llenar los campos con los datos obtenidos
          $('#editSolicitud').val(data.id_solicitud);
          $('#id_solicitud').val(data.id_solicitud);
          $('#cedula').val(data.cedula);
          $('#nombre').val(data.nombre);
          $('#telefono').val(data.telefono);
          $('#estado_civil').val(data.estado_civil);
          $('#tipo_vivienda').val(data.tipo_vivienda);
          $('#anos_habitar').val(data.anos_habitar);
          $('#direccion_domicilio').val(data.direccion_domicilio);
          $('#actividad_economica').val(data.actividad_economica);
          $('#rubro').val(data.rubro);
          $('#tipo_local').val(data.tipo_local);
          $('#tiempo_operar').val(data.tiempo_operar);
          $('#direccion_negocio').val(data.direccion_negocio);
          $('#cod_solicitud').val(data.cod_solicitud);
          

          if(data.estatus === 'Aprobada' || data.estatus === 'Rechazada'){

            disableActionButtons(`La solicitud ya fue ${data.estatus}.`);

            if(data.estatus === 'Aprobada'){

              Swal.fire({
                    icon: 'success',
                    title: 'La solicitud de crédito ya fue aprobada.',
                    text: `La solicitud ha sido aprobada y el préstamo puede ser desembolsado.`,
                    timer: 5000,
                    showConfirmButton: false
                });

            }else{

              Swal.fire({
                    icon: 'info',
                    title: 'La solicitud de crédito fue rechazada.',
                    text: 'La solicitud fue denegada por el comité de crédito.',
                    confirmButtonText: 'Entendido'
                });

               

            }
            

          }else{

                    if(data.estatus === 'Cancelado'){

                      //disableActionButtons(`La solicitud ya fue ${response.estatus}.`);

                    Swal.fire({
                          icon: 'info',
                          title: 'La solicitud de crédito ya fue cancelada.',
                          text: `El préstamo ha sido pagado en su totalidad y se ha cerrado.`,
                          timer: 5000,
                          showConfirmButton: false
                      });

                    }else{

                      Swal.fire({
                    icon: 'warning',
                    title: 'La solicitud de crédito esta pendiente.',
                    text: 'La solicitud está siendo evaluada por el comité de crédito.',
                    confirmButtonText: 'Entendido'
                });

                    }

            
          }

        },
        error: function() {
          alert('Hubo un error al cargar los datos.');
        }
      });

    const tabla = $('#tablaGarantias').DataTable({
      ajax: {
        url: 'servicio_garantia.php',
        type: 'POST',
        data: { accion: 'listarporid', id_solicitud: id},
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
      $('#editSolicitud').val(id);
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
