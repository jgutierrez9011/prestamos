<?php
require_once 'reg.php';
require_once '../../menu_builder.php';

// Función para limpiar y sanitizar datos
function limpiar($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Verificar sesión y permisos
/*session_start();
if (empty($_SESSION["user"])) {
    header("Location: ../../login.php");
    exit();
}*/

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['nombre'])) {
        $nombre = limpiar($_POST['nombre']);
        $conexion = $base_de_datos; // Asumiendo que reg.php establece esta conexión
        
        try {
            if (!empty($_POST['id'])) {
                // Actualizar perfil existente
                $id = (int)$_POST['id'];
                $sentencia = $conexion->prepare("UPDATE tblcatperfilusr SET strperfil = ? WHERE idperfil = ?");
                $sentencia->execute([$nombre, $id]);
                $_SESSION['mensaje'] = "Perfil actualizado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                // Crear nuevo perfil
                $conexion->beginTransaction();
                
                // Insertar perfil
                $sentencia = $conexion->prepare("INSERT INTO tblcatperfilusr(strperfil, bolactivo) VALUES (?, 'True') RETURNING idperfil");
                $sentencia->execute([$nombre]);
                $id_perfil = $sentencia->fetchColumn();
                
                // Insertar formularios asociados
                $stmt_form = $conexion->prepare("INSERT INTO tblcatperfilusrfrm (idfrm, idperfil, bolactivo) VALUES (?, ?, 'False')");
                $formularios = $conexion->query("SELECT idfrm FROM tblcatformularios");
                foreach ($formularios as $row) {
                    $stmt_form->execute(params: [$row['idfrm'], $id_perfil]);
                }
                
                // Insertar menús asociados
                $stmt_menu = $conexion->prepare("INSERT INTO tblcatmenuperfil(idperfil, intidmenu, bolactivo) VALUES (?, ?, 'False')");
                $menus = $conexion->query("SELECT intidmenu FROM tblcatmenu");
                foreach ($menus as $row) {
                    $stmt_menu->execute([$id_perfil, $row['intidmenu']]);
                }
                
                // Insertar detalles de formularios
                $stmt_detalle = $conexion->prepare("INSERT INTO tblcatperfilusrfrmdetalle(idfrmdetalle, idperfil, bolactivo) VALUES (?, ?, 'False')");
                $detalles = $conexion->query("SELECT idfrmdetalle FROM tblcatformulariodetalle ORDER BY idfrmdetalle, idfrm");
                foreach ($detalles as $row) {
                    $stmt_detalle->execute([$row['idfrmdetalle'], $id_perfil]);
                }
                
                $conexion->commit();
                $_SESSION['mensaje'] = "Perfil creado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            }
            
            // Redirigir para evitar reenvío del formulario
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $conexion->rollBack();
            $_SESSION['mensaje'] = "Error: " . $e->getMessage();
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Perfiles de Usuario</title>

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
  $menuBuilder = new MenuBuilder($base_de_datos, $_SESSION["user"]);
  echo $menuBuilder->buildMenu();
  ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Catálogo de perfiles</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Control de usuarios</a></li>
              <li class="breadcrumb-item active">Catálogo de perfiles</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Lista de perfiles de usuario</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        
        <div class="card-body">
          <!-- Mostrar mensajes -->
          <?php if (!empty($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              <?= $_SESSION['mensaje'] ?>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
          <?php endif; ?>

          <div class="row">
            <button class="btn btn-primary" data-toggle="modal" data-target="#newModal">
              <i class="fas fa-plus"></i> Crear nuevo perfil
            </button>
          </div>

          <br>

          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead class="thead-dark">
                <tr>
                  <th>Descripción de perfil</th>
                  <th width="20%">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $sentencia = $base_de_datos->query("SELECT idperfil, strperfil FROM tblcatperfilusr ORDER BY strperfil");
                $perfiles = $sentencia->fetchAll(PDO::FETCH_OBJ);
                
                foreach($perfiles as $perfil):
                  // Contar formularios activos para este perfil
                  $sentencia = $base_de_datos->prepare("SELECT COUNT(*) FROM tblcatperfilusrfrm WHERE idperfil = ? AND bolactivo = 'true'");
                  $sentencia->execute([$perfil->idperfil]);
                  $num_formularios = $sentencia->fetchColumn();
                  
                  $color = ($num_formularios == 0) ? 'btn-danger' : 'btn-primary';
                ?>
                <tr>
                  <td><?= htmlspecialchars($perfil->strperfil) ?></td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-default" data-toggle="modal" data-target="#editModal<?= $perfil->idperfil ?>">
                        <i class="fas fa-edit"></i> Editar
                      </button>
                      <a href="perfiladmin.php?id=<?= $perfil->idperfil ?>" class="btn <?= $color ?>">
                        <i class="fas fa-list-ul"></i> Admin
                      </a>
                    </div>
                  </td>
                </tr>

                <!-- Modal de Edición -->
                <div class="modal fade" id="editModal<?= $perfil->idperfil ?>">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title">Editar Perfil</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <form method="POST" action="">
                        <div class="modal-body">
                          <input type="hidden" name="id" value="<?= $perfil->idperfil ?>">
                          <div class="form-group">
                            <label>Descripción del perfil</label>
                            <input type="text" name="nombre" class="form-control" required 
                                   value="<?= htmlspecialchars($perfil->strperfil) ?>">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Modal de Creación -->
  <div class="modal fade" id="newModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Nuevo Perfil</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form method="POST" action="">
          <div class="modal-body">
            <div class="form-group">
              <label>Descripción del perfil</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Crear Perfil</button>
          </div>
        </form>
      </div>
    </div>
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