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
        <h1>Listado de Obligaciones Financieras</h1>
        <button class="btn btn-success" data-toggle="modal" data-target="#modalAgregar">Agregar Obligación</button>
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
           <!-- <label>ID Solicitud</label> -->
            <input type="hidden" name="id_solicitud" id="id_solicitud" class="form-control" readonly>
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
           <!-- <label>ID Solicitud</label> -->
            <input type="hidden" name="id_solicitud" id="editSolicitud" class="form-control" readonly>
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
<!-- SweetAlert2 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>

<script src="../../dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function () {

 // Obtener el parámetro de la URL (id o cédula)
 const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id_solicitud'); // Obtener el valor del parámetro 'id'

    // Realizar la solicitud AJAX
    $.ajax({
        url: `../../pages/prestamos/fnprestamos.php?id_solicitud=${id}`, // Enviar el ID como parámetro GET
        method: 'GET',
        success: function(response) {
          const data = response[0]; // extrae el primer objeto del array
          // Llenar los campos con los datos obtenidos
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

    const tabla = $('#tablaObligaciones').DataTable({
      ajax: {
        url: 'servicio_obligaciones.php',
        type: 'POST',
        data: { accion: 'listarporid', id_solicitud: id},
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
