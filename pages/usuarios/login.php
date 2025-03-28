<?php
/* Inicia nueva sesion */
require_once '../cn.php';

if(isset($_SESSION["user"])) {
    // Si la variable de session del usuario no es igual a vacio lo manda al index.php
    header("location: inicio.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Credimore | Log in</title>

  <style type="text/css">
      body {
          background-image: url(../image/ADI.jpg);
          background-size: cover;
          background-repeat: no-repeat;
          background-attachment: fixed;
          -moz-background-size: cover;
          -webkit-background-size: cover;
          -o-background-size: cover;
          min-height: 100vh;
          display: flex;
          justify-content: center;
          align-items: center;
          padding: 20px;
      }

      .login-container {
          width: 100%;
          max-width: 450px; /* Tamaño máximo para escritorio */
      }

      /* Para tablets */
      @media (min-width: 576px) and (max-width: 991.98px) {
          .login-container {
              max-width: 400px;
          }
      }

      /* Para móviles */
      @media (max-width: 575.98px) {
          .login-container {
              max-width: 100%;
              padding: 0 15px;
          }
          
          .imagen-responsiva {
              width: 150px;
              height: 150px;
          }
          
          .card-body {
              padding: 1.25rem;
          }
      }

      .transparente {
          opacity: 0.8;
          -moz-opacity: 0.8;
          filter: alpha(opacity=80);
          -khtml-opacity: 0.8;
      }

      .imagen-responsiva {
          width: 180px;
          height: 180px;
          max-width: 100%;
          height: auto;
          display: block;
          margin: 0 auto 20px auto;
      }

      .login-box-msg {
          font-size: 1.2rem;
          margin-bottom: 1.5rem;
      }

      .input-group {
          margin-bottom: 1.2rem;
      }

      .btn-block {
          padding: 0.5rem;
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
<div class="login-container">
  <div class="login-logo"></div>
  
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
        <img src="../image/Credimore.png" alt="Logo Credimore" class="imagen-responsiva">
      </div>
      
      <p class="login-box-msg">Iniciar sesión</p>

      <form name="frmlogin" id="frmlogin" method="post" action="fnloginusuario.php" onsubmit="return encriptarDatos()">
        <div class="input-group mb-3">
          <input type="text" autocomplete="off" class="form-control" name="usuario" id="usuario" placeholder="Usuario" value="jhonfc9011@gmail.com" required>
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
              <input type="checkbox" id="remember" name="remember">
              <label for="remember">Recuérdame</label>
            </div>
          </div>
          
          <div class="col-4">
            <button type="submit" id="enviarBtn" class="btn btn-primary btn-block login">Iniciar</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<script src="../../dist/js/admin.js"></script>

</body>
</html>