<?php
require_once 'reg.php';
require_once '../../menu_builder.php';

// Iniciar sesión y verificar autenticación
/*session_start();
if (empty($_SESSION["user"])) {
    header("Location: ../../login.php");
    exit();
}*/

// Función mejorada para limpiar datos
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Procesar cambios de estado
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['cambiomnu']) && !empty($_GET['es']) && !empty($_GET['cod'])) {
        // Cambiar estado de menú
        $id = (int)$_GET['cambiomnu'];
        $estado = $_GET['es'] === 'Activo' ? '0' : '1';
        $codigo = (int)$_GET['cod'];
        
        $sentencia = $base_de_datos->prepare("UPDATE tblcatmenuperfil SET bolactivo = ? WHERE intidmenuperfil = ?");
        $sentencia->execute([$estado, $id]);
        
        $_SESSION['mensaje'] = "Estado del menú actualizado correctamente";
        header('Location: perfiladmin.php?id='.$codigo);
        exit();
    }
    
    if (!empty($_GET['cambio']) && !empty($_GET['es']) && !empty($_GET['cod'])) {
        // Cambiar estado de formulario
        $id = (int)$_GET['cambio'];
        $estado = $_GET['es'] === 'Activo' ? '0' : '1';
        $codigo = (int)$_GET['cod'];
        
        $sentencia = $base_de_datos->prepare("UPDATE tblcatperfilusrfrm SET bolactivo = ? WHERE idperfilusrfrm = ?");
        $sentencia->execute([$estado, $id]);
        
        $_SESSION['mensaje'] = "Estado del formulario actualizado correctamente";
        header('Location: perfiladmin.php?id='.$codigo);
        exit();
    }
}

// Obtener datos del perfil
$perfil = null;
if (!empty($_GET['id'])) {
    $codigo = (int)$_GET['id'];
    
    $sentencia = $base_de_datos->prepare("SELECT c.strperfil 
          FROM tblcatperfilusrfrm as a
          INNER JOIN tblcatformularios as b ON a.idfrm = b.idfrm
          INNER JOIN tblcatperfilusr as c ON a.idperfil = c.idperfil
          WHERE a.idperfil = ? LIMIT 1");
    $sentencia->execute([$codigo]);
    $perfil = $sentencia->fetch(PDO::FETCH_OBJ);
    
    if (!$perfil) {
        $_SESSION['mensaje'] = "Perfil no encontrado";
        header('Location: perfil.php');
        exit();
    }
} else {
    header('Location: perfil.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Administrar Perfil</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
  <style>
    .menu-item { font-weight: bold; }
    .submenu-item { padding-left: 20px; }
    .status-btn { min-width: 80px; }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
  <?php 
  $menuBuilder = new MenuBuilder($base_de_datos, $_SESSION["user"]);
  echo $menuBuilder->buildMenu();
  ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Perfil: <?= htmlspecialchars($perfil->strperfil) ?></h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="perfil.php">Control de usuarios</a></li>
              <li class="breadcrumb-item active">Administrar perfil</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-edit"></i>
            Configuración del Perfil
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        
        <div class="card-body">
          <!-- Mostrar mensajes -->
          <?php if (!empty($_SESSION['mensaje'])): ?>
            <div class="alert alert-info alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              <?= $_SESSION['mensaje'] ?>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
          <?php endif; ?>

          <!-- Nav tabs -->
          <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="menu-tab" data-toggle="tab" href="#menu" role="tab">
                <i class="fas fa-bars"></i> Gestión de Menús
              </a>
            </li>
          </ul>

          <!-- Tab panes -->
          <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="menu" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead class="thead-dark">
                    <tr>
                      <th>Elemento del Menú</th>
                      <th width="150px">Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Obtener menús principales
                    $sentencia = $base_de_datos->prepare("SELECT a.intidmenuperfil, b.strmenu, a.bolactivo, b.strtipomenu
                          FROM tblcatmenuperfil as a
                          INNER JOIN tblcatmenu as b ON a.intidmenu = b.intidmenu
                          INNER JOIN tblcatperfilusr as c ON a.idperfil = c.idperfil
                          WHERE a.idperfil = ? AND b.strnivelmenu = '1'");
                    $sentencia->execute([$codigo]);
                    $menus = $sentencia->fetchAll(PDO::FETCH_OBJ);

                    foreach($menus as $menu):
                        $activo = $menu->bolactivo == '1';
                        $color = $activo ? 'btn-success' : 'btn-danger';
                        $estado = $activo ? 'Activo' : 'Inactivo';
                    ?>
                    <tr class="menu-item">
                      <td><?= htmlspecialchars($menu->strmenu) ?></td>
                      <td class="text-center">
                        <a href="perfiladmin.php?cambiomnu=<?= $menu->intidmenuperfil ?>&es=<?= $estado ?>&cod=<?= $codigo ?>" 
                           class="btn btn-sm status-btn <?= $color ?>">
                          <?= $estado ?>
                        </a>
                      </td>
                    </tr>
                    
                    <?php
                    // Obtener submenús/formularios para este menú
                    $sentencia_sub = $base_de_datos->prepare("SELECT a.idperfilusrfrm, b.strformulario, a.bolactivo, a.idfrm, a.idperfil
                             FROM tblcatperfilusrfrm as a
                             INNER JOIN tblcatformularios as b ON a.idfrm = b.idfrm
                             INNER JOIN tblcatperfilusr as c ON a.idperfil = c.idperfil
                             WHERE a.idperfil = ? AND b.strkeymenu = ?
                             ORDER BY a.idperfilusrfrm ASC");
                    $sentencia_sub->execute([$codigo, $menu->strtipomenu]);
                    $submenus = $sentencia_sub->fetchAll(PDO::FETCH_OBJ);
                    
                    foreach ($submenus as $submenu):
                        $activo_sub = $submenu->bolactivo == '1';
                        $color_sub = $activo_sub ? 'btn-success' : 'btn-danger';
                        $estado_sub = $activo_sub ? 'Activo' : 'Inactivo';
                    ?>
                    <tr class="submenu-item">
                      <td>
                        <a href="perfiladmindet.php?formdet=<?= $submenu->idfrm ?>&perfil=<?= $submenu->idperfil ?>">
                          <i class="fas fa-file-alt"></i> <?= htmlspecialchars($submenu->strformulario) ?>
                        </a>
                      </td>
                      <td class="text-center">
                        <a href="perfiladmin.php?cambio=<?= $submenu->idperfilusrfrm ?>&es=<?= $estado_sub ?>&cod=<?= $codigo ?>" 
                           class="btn btn-sm status-btn <?= $color_sub ?>">
                          <?= $estado_sub ?>
                        </a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              
              <div class="mt-3">
                <a href="perfil.php" class="btn btn-info">
                  <i class="fas fa-arrow-left"></i> Volver a Perfiles
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php require_once '../../footer.php'; ?>
</div>

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<script>
// Cerrar automáticamente las alertas después de 5 segundos
$(document).ready(function() {
  setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
      $(this).remove(); 
    });
  }, 5000);
});
</script>
</body>
</html>