<?php
require_once  'reg.php';
require_once 'fnusuario.php';

$usuario = $_SESSION["user"];

if (isset($_POST["btnguardar"]))
{
  actualizar_usuario($_POST['idusuario'],strtoupper($_POST['pnombreedit']), strtoupper($_POST["snombre"]), strtoupper($_POST["papellido"]), strtoupper($_POST["sapellido"]),
                     $_POST["idn"], $_POST["correo"], $_POST["telefono"], $_POST["sexo"], $_POST["direccion"], $_POST["perfil"],$_POST["password"],$_POST["cartera"],$_POST["sucursal"],$_POST["usuario"],$base_de_datos);
}

$idusuario = base64_decode($_GET["id"]);

$sentencia = $base_de_datos->query("SELECT * from tblcatusuario where intid = $idusuario");
$usuarios = $sentencia->fetch(PDO::FETCH_OBJ);
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
<?php require_once '../../menu.php'; ?>
<!-- TERMINA EL MENU -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Editar usuario</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="catusuarios.php">Catálogo de usuarios</a></li>
              <li class="breadcrumb-item active">Editar usuario</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>



    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="col-md-12 mb-12">
        <?php
      if(isset($_GET["token"])):
        if($_GET["token"] == 1):
          echo "<div class='alert alert-success'>
               <strong>Exito!</strong> El usuario fue actualizado con exito!
               </div>";
        elseif($_GET["token"] == 2):
          echo $_SESSION["sentencia"];
          echo "<div class='alert alert-warning'>
                <strong>Error!</strong> Disculpe no se actualizo el usuario!, por favor verifique.
                </div>";
        endif;
      endif;
         ?>
       </div>
      </div>

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Información general de usuario</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">

          <!-- Datos del fromulario-->
          <form class="needs-validation" action="usuariosedit.php" method="post">
          <!-- Primera linea de campos en el fromulario-->

          <div class="row">
              <div class="col-md-3 mb-3">
                <label for="primernombre">Primer nombre</label>
                <input type="text" class="form-control form-control-sm" id="pnombre" name="pnombreedit" value="<?php echo $usuarios->strpnombre ?>" required>
                <input type="hidden" class="form-control form-control-sm" id="idusuario" name="idusuario" value="<?php echo $idusuario ?>" required>
              </div>

              <div class="col-md-3 mb-3">
                <label for="segundonombre">Segundo nombre</label>
                <input type="text" class="form-control form-control-sm" id="snombre" name="snombre" value="<?php echo $usuarios->strsnombre ?>">
              </div>

              <div class="col-md-3 mb-3">
                <label for="primerapellido">Primer apellido</label>
                <input type="text" class="form-control form-control-sm" id="papellido" name="papellido" value="<?php echo $usuarios->strpapellido ?>" required>
              </div>

              <div class="col-md-3 mb-3">
                <label for="segundoapellido">Segundo apellido</label>
                <input type="text" class="form-control form-control-sm" id="sapellido" name="sapellido" value="<?php echo $usuarios->strsapellido ?>">
              </div>
          </div>


          <!-- Segunda linea de campos en el formulario-->

          <div class="row ">

            <div class="col-md-3 mb-3">
              <label for="Sexo">Sexo</label>
              <select class="form-control form-control-sm" id="sexo" name="sexo" required>
                        <option value="">Seleccione</option>
                          <option <?php if($usuarios->strsexo == "FEMENINO") echo 'selected';?> value="FEMENINO">FEMENINO</option>
                            <option <?php if($usuarios->strsexo == "MASCULINO") echo 'selected';?> value="MASCULINO">MASCULINO</option>

              </select>
            </div>

            <div class="col-md-3 mb-3">
                 <label for="identificacion">Identificación</label>
                 <input type="text" class="form-control form-control-sm" id="idn" name="idn" value="<?php echo $usuarios->stridentificacion ?>">
                 <div id="mensaje"></div>
            </div>

            <div class="col-md-3 mb-3">
                 <label for="telefono">Telefono</label>
                 <input type="text" class="form-control form-control-sm" id="telefono" name="telefono" maxlength="8" value="<?php echo $usuarios->strcontacto ?>">
            </div>

            <div class="col-md-3 mb-3">

              <label for="Correo">Correo </label>
              <input type="text" class="form-control form-control-sm" id="correo" name="correo" maxlength="8" required value="<?php echo $usuarios->strcorreo ?>">

            </div>


          </div>


          <!-- Tercera linea de campos en el formulario-->

          <div class="row ">

            <div class="col-md-3 mb-3">

              <label for="Correo">Usuario</label>
              <input type="text" class="form-control form-control-sm" id="usuario" name="usuario" maxlength="8" required value="<?php echo $usuarios->strusuario ?>">

            </div>

            <div class="col-md-3 mb-3">

              <label for="Correo">Contraseña</label>
                 <input class="form-control form-control-sm" placeholder="Contraseña" name="password" type="text" data-rule-required="true" data-rule-minlength="6">

             </div>

           <div class="col-md-3 mb-3">
             <label for="Sexo">Perfil de usuario</label>
             <select class="form-control form-control-sm" id="perfil" name="perfil" required>
                   <?php echo fillperfil_usuario($usuarios->intidperfil,$base_de_datos) ?>
             </select>
           </div>

           <div class="col-md-3 mb-3">
             <label for="lblcartera">Cartera</label>
             <select class="form-control form-control-sm" id="cartera" name="cartera">
                   <?php echo fillcartera_usuario($usuarios->idcartera,$base_de_datos) ?>
             </select>
           </div>

          </div>

          <div class="row">

              <div class="col-md-3 mb-3">
                <label for="lblsucursal">Sucursal</label>
                <select class="form-control form-control-sm" id="sucursal" name="sucursal">
                      <?php echo fillsucursales($usuarios->sucursal_id,$base_de_datos) ?>
                </select>
              </div>

          </div>

          <div class="row">

          <div class="col-md-12 mb-3">
          <label for="comentario">Dirección</label>
          <textarea class="form-control form-control-sm text-uppercase font-italic" id="direccion" name="direccion" text="arial-label"><?php echo $usuarios->strdireccion ?></textarea>
          </div>

          </div>

          <hr class="mb-4">

          <div class="row">

          <div class="col-md-4">
          <button class="btn btn-primary btn-sm" name="btnguardar" type="submit" value="btnguardar">Guardar</button>
          <a href="catusuarios.php" class="btn btn-primary btn-sm" role="button">Buscar</a>
          </div>

          </div>

          </form>

        </div>
        <!-- /.card-body -->
        <div class="card-footer">

        </div>
        <!-- /.card-footer-->
      </div>
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
</body>
</html>
