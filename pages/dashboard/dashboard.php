<?php
require_once '../usuarios/reg.php';
require_once '../../menu_builder.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Total Abonos</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
  <?php
  if (!empty($_SESSION["user"])) {
    $menuBuilder = new MenuBuilder($base_de_datos, $_SESSION["user"]);
    echo $menuBuilder->buildMenu();
  }
  ?>
  <!-- /.navbar -->

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Dashboard de Abonos</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Inicio</a></li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="row">
        <!-- Abonos Realizados -->
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-hand-holding-usd"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Abonos</span>
              <span class="info-box-number" id="total-abonos">
                C$ 0.00
              </span>
            </div>
          </div>
        </div>
        <!-- /.col -->
        <!-- Saldo pendiente -->
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-hand-holding-usd"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Saldo pendiente</span>
              <span class="info-box-number" id="saldo-pendiente">
                C$ 0.00
              </span>
            </div>
          </div>
        </div>
        <!-- /.col -->
         <!-- Interes colocado -->
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-hand-holding-usd"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Interes pendiente</span>
              <span class="info-box-number" id="interes-pendiente">
                C$ 0.00
              </span>
            </div>
          </div>
        </div>
        <!-- /.col -->
      </div>
    </section>
  </div>

  <?php require_once '../../footer.php'; ?>

  <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>

<script>
  // Llamar al servicio para obtener el total de abonos
  fetch('dashboard_service.php')
  .then(response => response.json())
  .then(data => {
    const montoAbonos = parseFloat(data.total_abonado).toLocaleString('es-NI', {
      style: 'currency',
      currency: 'NIO',
      minimumFractionDigits: 2
    });

    const saldoPendiente = parseFloat(data.saldo_pendiente).toLocaleString('es-NI', {
      style: 'currency',
      currency: 'NIO',
      minimumFractionDigits: 2
    });

    const interesPendiente = parseFloat(data.interes_pendiente).toLocaleString('es-NI', {
      style: 'currency',
      currency: 'NIO',
      minimumFractionDigits: 2
    });

    document.getElementById('total-abonos').textContent = montoAbonos;
    document.getElementById('saldo-pendiente').textContent = saldoPendiente;
    document.getElementById('interes-pendiente').textContent = interesPendiente;
  })
  .catch(error => {
    console.error('Error al obtener los datos del dashboard:', error);
    document.getElementById('total-abonos').textContent = 'Error';
    document.getElementById('saldo-pendiente').textContent = 'Error';
    document.getElementById('interes-pendiente').textContent = 'Error';
  });

    
</script>
</body>
</html>
