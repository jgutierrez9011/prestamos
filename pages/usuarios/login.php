<?php
/* Inicia nueva sesion */
require_once '../cn.php';

if(isset($_SESSION["user"]))
{
//Si la variable de session del usuario no es igual a vacio lo manda al index.php
	header("location: inicio.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Credimore|Log in</title>

	<style type="text/css">
      body {
          	background-image: url(../image/ADI.jpg);
						background-size: cover;
						background-repeat: no-repeat;
						background-attachment: fixed;
            -moz-background-size: cover;
						-webkit-background-size: cover;
						-o-background-size: cover;

            }

						.transparente{
			opacity: 0.8;
			-moz-opacity: 0.8;
			filter: alpha(opacity=80);
			-khtml-opacity: 0.8;
			}

      .imagen-responsiva {
            width: 200px; /* Ancho fijo */
            height: 200px; /* Alto fijo */
            max-width: 100%; /* Hace que la imagen sea responsiva */
            height: auto; /* Mantiene la proporción de la imagen */
            display: block; /* Para evitar espacios no deseados */
            margin: 0 auto; /* Centra la imagen horizontalmente */
        }
    </style>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
  <!--  <a href="../../index2.html"><b>Admin</b>LTE</a>  -->
  </div>
  <!-- /.login-logo -->
  <div class="row">
      <div class="col-md-12 mb-12">
         <div id="resultados_ajax"></div>
      </div>
  </div>
  <?php if(isset($_GET['token'])){ ?>
    <div class='alert alert-warning alert-dismissible alerta'>
        <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
        <strong>¡Ups!</strong> Verifique el usuario y la contraseña introducidos, si no reporta al administrador.
        </div>
  <?php }; ?>

  <div class="card">
    <div class="card-body login-card-body">
      <div class="contenedor-imagen">
        <img src="../image/Credimore.png" alt="Descripción de la imagen" class="imagen-responsiva">
    </div>
			<!-- <p class="login-box-msg">Credimore</p> -->
      <p class="login-box-msg">Iniciar sesión</p>

      <form name="frmlogin" id="frmlogin" method="post" action="fnloginusuario.php" onsubmit="return encriptarDatos()">
        <div class="input-group mb-3">
          <input type="text" autocomplete="off" class="form-control"  name="usuario" id="usuario" placeholder="Usuario" value="jhonfc9011@gmail.com" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" autocomplete="off" class="form-control" name="passw" id="passw" placeholder="Password" value="12345" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="remember"  name="remember">
              <label for="remember">
                Recuerdame
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <!-- <input type="hidden" name="encabezado" id="encabezado"> -->
            <button type="submit" id="enviarBtn" class="btn btn-primary btn-block login">Iniciar</button>
          </div> 
          <!-- /.col -->
        </div>
      </form>
       
     <!-- <form action="fnloginusuario.php" method="post" onsubmit="return encriptarDatos()">
      <input type="hidden" name="encabezado" id="encabezado">
      <button type="submit" class="btn btn-primary btn-block">Iniciar</button>
      </form> -->
      



    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<script src="../../dist/js/admin.js"></script>

</body>
</html>
