<?php
require_once  '../usuarios/reg.php';
require_once '../../menu_builder.php';
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
            <h1>Agregar cliente</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Clientes</a></li>
              <li class="breadcrumb-item active">Nuevo cliente</li>
            </ol>
          </div>
        </div>

        <!--<div class="card">
            <div class="card-header bg-primary text-white">Formulario de Clientes</div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label for="cedula">Cédula</label>
                        <input type="text" class="form-control" id="cedula" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" id="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" required>
                    </div>
                    <div class="form-group">
                        <label for="estado_civil">Estado Civil</label>
                        <select class="form-control" id="estado_civil" required>
                            <option value="">Seleccione...</option>
                            <option value="soltero">Soltero</option>
                            <option value="casado">Casado</option>
                            <option value="union de hecho">Unión de Hecho</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select class="form-control" id="tipo" required>
                            <option value="">Seleccione...</option>
                            <option value="Cliente nuevo">Cliente Nuevo</option>
                            <option value="recurrente">Recurrente</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="actividad_economica">Actividad Económica</label>
                        <input type="text" class="form-control" id="actividad_economica" required>
                    </div>
                    <div class="form-group">
                        <label for="direccion_domicilio">Dirección del Domicilio</label>
                        <input type="text" class="form-control" id="direccion_domicilio" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo_vivienda">Tipo de Vivienda</label>
                        <select class="form-control" id="tipo_vivienda" required>
                            <option value="">Seleccione...</option>
                            <option value="propio">Propio</option>
                            <option value="renta">Renta</option>
                            <option value="albergue">Albergue</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="anos_habitar">Años de Habitar</label>
                        <input type="number" class="form-control" id="anos_habitar" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="direccion_negocio">Dirección del Negocio</label>
                        <input type="text" class="form-control" id="direccion_negocio" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo_local">Tipo de Local</label>
                        <select class="form-control" id="tipo_local" required>
                            <option value="">Seleccione...</option>
                            <option value="propio">Propio</option>
                            <option value="renta">Renta</option>
                            <option value="albergue">Albergue</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tiempo_operar">Tiempo de Operar (mínimo 6 meses)</label>
                        <input type="number" class="form-control" id="tiempo_operar" min="6" required>
                    </div>
                    <div class="form-group">
                        <label for="rubro">Rubro</label>
                        <select class="form-control" id="rubro" required>
                            <option value="">Seleccione...</option>
                            <option value="comercio">Comercio</option>
                            <option value="servicio">Servicio</option>
                            <option value="produccion">Producción</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>-->

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
                                <input type="text" class="form-control form-control-sm" id="cedula" name="cedula" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control form-control-sm" id="telefono" name="telefono" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="estado_civil">Estado Civil</label>
                                <select class="form-control form-control-sm" id="estado_civil" name="estado_civil" required>
                                    <option value="">Seleccione...</option>
                                    <option value="soltero">Soltero</option>
                                    <option value="casado">Casado</option>
                                    <option value="union de hecho">Unión de Hecho</option>
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
                                    <option value="propio">Propio</option>
                                    <option value="renta">Renta</option>
                                    <option value="albergue">Albergue</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="anos_habitar">Años de Habitar</label>
                                <input type="number" class="form-control form-control-sm" id="anos_habitar" name="anos_habitar" min="0" required>
                            </div>
                            
                        </div>
                        <div class="form-row">
                        <div class="form-group col-md-12">
                                <label for="direccion_domicilio">Dirección del Domicilio</label>
                                <input type="text" class="form-control form-control-sm" id="direccion_domicilio" name="direccion_domicilio" required>
                            </div>
                        </div>
                        <div class="form-row">
                        <div class="form-group col-md-4">
                                <label for="actividad_economica">Actividad Económica</label>
                                <input type="text" class="form-control form-control-sm" id="actividad_economica" name="actividad_economica" required>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="rubro">Rubro</label>
                                <select class="form-control form-control-sm" id="rubro" name="rubro" required>
                                    <option value="">Seleccione...</option>
                                    <option value="comercio">Comercio</option>
                                    <option value="servicio">Servicio</option>
                                    <option value="produccion">Producción</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="tipo_local">Tipo de Local</label>
                                <select class="form-control form-control-sm" id="tipo_local" name="tipo_local" required>
                                    <option value="">Seleccione...</option>
                                    <option value="propio">Propio</option>
                                    <option value="renta">Renta</option>
                                    <option value="albergue">Albergue</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="tiempo_operar">Tiempo de Operar (mínimo 6 meses)</label>
                                <input type="number" class="form-control form-control-sm" id="tiempo_operar" name="tiempo_operar" min="6" required>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="direccion_negocio">Dirección del Negocio</label>
                                <input type="text" class="form-control form-control-sm" id="direccion_negocio" name="direccion_negocio" required>
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
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>

<script>
        $(document).ready(function() {

            $("#clienteForm").submit(function(event) {
                
                event.preventDefault();
                
                // Crear un objeto con los datos del formulario
                var formData = {
                cedula: $("#cedula").val(),
                nombre: $("#nombre").val(),
                telefono: $("#telefono").val(),
                estado_civil: $("#estado_civil").val(),
                //tipo: $("#tipo").val(),
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
                
                $.ajax({
                    type: "POST",
                    url: "fncliente.php",
                    data: jsonData,
                    contentType: "application/json", // Indicar que se envía JSON
                    success: function(response) {
                        alert("Cliente guardado exitosamente");
                        $("#clienteForm")[0].reset();
                    },
                    error: function() {
                        alert("Hubo un error al guardar el cliente");
                    }
                });
            });
        });
    </script>
</body>
</html>
