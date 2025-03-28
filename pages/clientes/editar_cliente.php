<?php
require_once  '../usuarios/reg.php';
require_once '../../menu_builder.php';

// Obtener el ID del cliente desde la URL
$clienteId = isset($_GET['idcliente']) ? $_GET['idcliente'] : '';

if (!$clienteId) {
    echo "ID de cliente no válido.";
    exit;
}



$url = BASE_URL . "/pages/clientes/fncliente.php?idcliente=" . $clienteId;


// Llamar al API para obtener los datos del cliente
$response = file_get_contents($url);
$cliente = json_decode($response, true);
//$cliente = reset($cliente);

//echo var_dump($cliente);



if ($cliente === null) {
  echo "Error al decodificar JSON: " . json_last_error_msg();
  exit;
}

if (!$cliente) {
    echo "Error al obtener los datos del cliente.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Blank Page</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
<!-- INICIA EL MENU -->
<?php //require_once '../../menu.php';
if (!empty($_SESSION["user"])) {
  $menuBuilder = new MenuBuilder($base_de_datos, $_SESSION["user"]);
  echo $menuBuilder->buildMenu();
}
?>
<!-- TERMINA EL MENU -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Catálago de clientes</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Catalogo</a></li>
              <li class="breadcrumb-item active">Clientes</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>



    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
     <div class="card">
        <div class="card-header bg-primary text-white">
          <h3 class="card-title">Nuevo cliente</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
           <!-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
              <i class="fas fa-times"></i>
            </button> -->
          </div>
        </div>
        <div class="card-body">
        <form id="clienteForm">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="cedula">Cédula</label>
                                <input type="hidden" class="form-control form-control-sm" id="id_cliente" name="id_cliente" value="<?php echo htmlspecialchars($cliente["idcliente"] ?? ''); ?>" required>
                                <input type="text" class="form-control form-control-sm" id="cedula" name="cedula" value="<?php echo htmlspecialchars($cliente["cedula"] ?? ''); ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente["nombre"] ?? ''); ?>" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control form-control-sm" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente["telefono"] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="estado_civil">Estado Civil</label>
                                <select class="form-control form-control-sm" id="estado_civil" name="estado_civil" required>
                                    <option value="">Seleccione...</option>
                                    <option value="soltero" <?php if ($cliente["estado_civil"] == 'soltero') echo 'selected'; ?>>Soltero</option>
                                    <option value="casado" <?php if ($cliente["estado_civil"] == 'casado') echo 'selected'; ?>>Casado</option>
                                    <option value="union de hecho" <?php if ($cliente["estado_civil"] == 'union de hecho') echo 'selected'; ?>>Unión de Hecho</option>
                                </select>
                            </div>
                            <!--<div class="form-group col-md-4">
                                <label for="tipo">Tipo</label>
                                <select class="form-control form-control-sm" id="tipo" name="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Cliente nuevo">Cliente Nuevo</option>
                                    <option value="recurrente">Recurrente</option>
                                </select>
                            </div> -->
                            <div class="form-group col-md-4">
                                <label for="tipo_vivienda">Tipo de Vivienda</label>
                                <select class="form-control form-control-sm" id="tipo_vivienda" name="tipo_vivienda" required>
                                    <option value="">Seleccione...</option>
                                    <option value="propio" <?php if ($cliente["tipo_vivienda"] == 'propio') echo 'selected'; ?>>Propio</option>
                                    <option value="renta" <?php if ($cliente["tipo_vivienda"] == 'renta') echo 'selected'; ?>>Renta</option>
                                    <option value="albergue" <?php if ($cliente["tipo_vivienda"] == 'albergue') echo 'selected'; ?>>Albergue</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="anos_habitar">Años de Habitar</label>
                                <input type="number" class="form-control form-control-sm" id="anos_habitar" name="anos_habitar" min="0" value="<?php echo htmlspecialchars($cliente["anos_habitar"] ?? ''); ?>" required>
                            </div>
                            
                        </div>
                        <div class="form-row">
                        <div class="form-group col-md-12">
                                <label for="direccion_domicilio">Dirección del Domicilio</label>
                                <input type="text" class="form-control form-control-sm" id="direccion_domicilio" name="direccion_domicilio" value="<?php echo htmlspecialchars($cliente["direccion_domicilio"] ?? ''); ?>"  required>
                            </div>
                        </div>
                        <div class="form-row">
                        <div class="form-group col-md-4">
                                <label for="actividad_economica">Actividad Económica</label>
                                <input type="text" class="form-control form-control-sm" id="actividad_economica" name="actividad_economica" value="<?php echo htmlspecialchars($cliente["actividad_economica"] ?? ''); ?>" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="rubro">Rubro</label>
                                <select class="form-control form-control-sm" id="rubro" name="rubro" required>
                                    <option value="">Seleccione...</option>
                                    <option value="comercio" <?php if ($cliente["rubro"] == 'comercio') echo 'selected'; ?>>Comercio</option>
                                    <option value="servicio" <?php if ($cliente["rubro"] == 'servicio') echo 'selected'; ?>>Servicio</option>
                                    <option value="produccion" <?php if ($cliente["rubro"] == 'produccion') echo 'selected'; ?>>Producción</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="tipo_local">Tipo de Local</label>
                                <select class="form-control form-control-sm" id="tipo_local" name="tipo_local" required>
                                    <option value="">Seleccione...</option>
                                    <option value="propio" <?php if ($cliente["tipo_local"] == 'propio') echo 'selected'; ?>>Propio</option>
                                    <option value="renta" <?php if ($cliente["tipo_local"] == 'renta') echo 'selected'; ?>>Renta</option>
                                    <option value="albergue" <?php if ($cliente["tipo_local"] == 'albergue') echo 'selected'; ?>>Albergue</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="tiempo_operar">Tiempo de Operar (mínimo 6 meses)</label>
                                <input type="number" class="form-control form-control-sm" id="tiempo_operar" name="tiempo_operar" min="6" value="<?php echo htmlspecialchars($cliente["tiempo_operar"] ?? ''); ?>" required>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="direccion_negocio">Dirección del Negocio</label>
                                <input type="text" class="form-control form-control-sm" id="direccion_negocio" name="direccion_negocio"  value="<?php echo htmlspecialchars($cliente["direccion_negocio"] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="clientes.php" class="btn btn-primary" role="button">Buscar</a>
                        </div>
                        
                    </form>
        </div>
        <!--  /.card-body -->
        <div class="card-footer">
          Footer
        </div>
        <!-- /.card-footer-->
     <!-- </div> -->
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- FOOTER -->
  <?php require_once '../../footer.php'; ?>
  <!-- FOOTER -->

  <!-- Modal -->
  <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">Activar/Inactivar usuario</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <form role="form" action="fnusuario.php" method="post">

                 <div class="form-group">
                     <label for="inputName">Fecha</label>
                     <input type="date" class="form-control" id="fechabaja" name="fechabaja" required/>
                 </div>

                 <div class="form-group">
                     <input type="hidden" class="form-control" id="idempleado" name="idempleado"/>
                     <input type="hidden" class="form-control" id="estado_usuario" name="estado_usuario"/>
                 </div>

                 <input type="submit" name="inactivar" id="inactivar" value="Desactivar" class="btn btn-danger"/>

                 <input type="submit" name="activar" id="activar" value="Activar" class="btn btn-success"/>

          </form>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>
<!-- Page specific script -->
<script>
  $(function () {
    
    $("#clienteForm").submit(function(event) {
    event.preventDefault();

    // Crear un objeto con los datos del formulario
    var formData = {
        idcliente: $("#id_cliente").val(),
        cedula: $("#cedula").val(), // La cédula es obligatoria para identificar el registro
        nombre: $("#nombre").val(),
        telefono: $("#telefono").val(),
        estado_civil: $("#estado_civil").val(),
        actividad_economica: $("#actividad_economica").val(),
        direccion_domicilio: $("#direccion_domicilio").val(),
        tipo_vivienda: $("#tipo_vivienda").val(),
        anos_habitar: $("#anos_habitar").val(),
        direccion_negocio: $("#direccion_negocio").val(),
        tipo_local: $("#tipo_local").val(),
        tiempo_operar: $("#tiempo_operar").val(),
        rubro: $("#rubro").val(),
    };

    // Convertir el objeto a JSON
    var jsonData = JSON.stringify(formData);

    // Realizar la solicitud PUT para actualizar el cliente
    $.ajax({
        type: "PUT", // Método HTTP PUT
        url: "fncliente.php", // Reemplazar con la URL de tu API
        data: jsonData, // Datos en el cuerpo de la solicitud
        contentType: "application/json", // Indicar que se envía JSON
        dataType: "json", // Esperar una respuesta en formato JSON
        success: function(response) {
            if (response.message) {
                alert(response.message); // Mostrar mensaje de éxito
                $("#clienteForm")[0].reset(); // Limpiar el formulario
                location.reload(); // Recargar la página
            } else if (response.error) {
                alert("Error: " + response.error); // Mostrar mensaje de error
            }
        },
        error: function(xhr, status, error) {
            alert("Hubo un error al actualizar el cliente: " + error); // Manejar errores de la solicitud
        }
    });
});
    
});

</script>
</body>
</html>
