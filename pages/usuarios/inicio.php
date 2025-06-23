<?php
require_once 'reg.php';
require_once '../../menu_builder.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Inicio</title>

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">

  <style>
    .logo-centrado {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }
    .logo-centrado img {
      max-width: 100%;
      height: auto;
      max-height: 250px;
    }
    .card-title i {
      margin-right: 8px;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Menú lateral -->
  <?php
    if (!empty($_SESSION["user"])) {
      $menuBuilder = new MenuBuilder($base_de_datos, $_SESSION["user"]);
      echo $menuBuilder->buildMenu();
    }
  ?>

  <!-- Contenido principal -->
  <div class="content-wrapper">

    <!-- Encabezado de contenido -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1><i class="fas fa-home text-primary"></i> Página de Inicio</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Inicio</a></li>
              <li class="breadcrumb-item active">Bienvenido</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Contenido -->
    <section class="content">
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h3 class="card-title"><i class="fas fa-info-circle"></i> Bienvenido al Sistema</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool text-white" data-card-widget="remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="logo-centrado">
            <img src="../image/Credimore.png" alt="Logo del sistema Credimore">
          </div>
          <p class="text-center mt-4">Bienvenido(a) <strong><?php echo $_SESSION["nombreusuario"] ?? 'Usuario'; ?></strong> al sistema Credimore.</p>
        </div>
        <div class="card-footer text-muted text-center">
          Sistema desarrollado por el equipo de TI
        </div>
      </div>
    </section>
  </div>

  <!-- Footer -->
  <?php require_once '../../footer.php'; ?>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<!-- Scripts -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../dist/js/adminlte.min.js"></script>
</body>
</html>
