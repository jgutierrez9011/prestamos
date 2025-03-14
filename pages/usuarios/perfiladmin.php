<?php
require_once  'reg.php';

function limpiar($tags){
  $tags = trim($tags);
  return $tags;
}

if(!empty($_GET['id']))
{

  $codigo = $_GET['id'];
  $usuario = $_SESSION['user'];

     /*Muestra las pantallas por perfil*/
  $sentencia = $base_de_datos->query("SELECT a.idperfilusrfrm, b.strformulario, a.bolactivo, c.strperfil
        FROM tblcatperfilusrfrm as a
        inner join tblcatformularios as b on a.idfrm = b.idfrm
        inner join tblcatperfilusr as c on a.idperfil = c.idperfil
        WHERE a.idperfil= $codigo");
  $pantallas = $sentencia->fetch(PDO::FETCH_OBJ);


}

if((!empty($_GET['cambiomnu'])) && (!empty($_GET['es'])))
{

  $cambio=limpiar($_GET['cambiomnu']);
  $estado_u=limpiar($_GET['es']);
  $id_usu=limpiar($_GET['cod']);
  if($estado_u =='Activo'){

    $sentencia = $base_de_datos->prepare("UPDATE tblcatmenuperfil SET bolactivo= '0'WHERE intidmenuperfil = $cambio");
    $sentencia->execute();

  }else{

    $sentencia = $base_de_datos->prepare("UPDATE tblcatmenuperfil SET bolactivo= '1' WHERE intidmenuperfil = $cambio");
    $sentencia->execute();

  }
  header('Location: perfiladmin.php?id='.$id_usu);
}


if((!empty($_GET['cambio'])) && (!empty($_GET['es'])))
{

  $cambio=limpiar($_GET['cambio']);
  $estado_u=limpiar($_GET['es']);
  $id_usu=limpiar($_GET['cod']);
  if($estado_u =='Activo'){

    $sentencia = $base_de_datos->prepare("UPDATE tblcatperfilusrfrm SET bolactivo  =  '0' WHERE idperfilusrfrm = $cambio");
    $sentencia->execute();

  }else{

    $sentencia = $base_de_datos->prepare("UPDATE tblcatperfilusrfrm SET bolactivo  =  '1' WHERE idperfilusrfrm = $cambio");
    $sentencia->execute();

  }
  header('Location: perfiladmin.php?id='.$id_usu);
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
            <h1>Editar perfil</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="perfil.php">Control de usuarios</a></li>
              <li class="breadcrumb-item active">Editar perfil</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>



    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-edit"></i>
            Perfil : <?php echo $pantallas->strperfil; ?></h3>

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

          <!-- Nav tabs -->
          <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="home" data-toggle="pill" href="#home" role="tab" aria-controls="home" aria-selected="true">Menú Management</a>
              </li>
              <li><a href="#profile" data-toggle="tab">  </a>
              </li>
          </ul>

          <!-- Tab panes -->
          <div class="tab-content">
              <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home">
                  <br>
                  <!--Tabla que muestra los menus por perfil-->
                  <div class="row table-responsive">
                    <table class="table table-bordered">
                      <tr class="well">
                            <td><strong>Descripcion del menu</strong></td>
                            <td width="20%"></td>
                      </tr>
                        <?php
                        $sentencia = $base_de_datos->query("SELECT a.intidmenuperfil, b.strmenu, a.bolactivo, c.strperfil, b.strtipomenu
                              from tblcatmenuperfil as a
                              inner join tblcatmenu as b on a.intidmenu = b.intidmenu
                              inner join tblcatperfilusr as c on a.idperfil = c.idperfil
                              where a.idperfil = $codigo and (b.strnivelmenu = '1')");
                        $lista_menu = $sentencia->fetchAll(PDO::FETCH_OBJ);

                    foreach($lista_menu as $row_menu){

                      if($row_menu->bolactivo ==  0){

                        $color_menu  ='btn btn-danger btn-xs';
                        $estado_menu = 'Inactivo';

                      }else{

                        $color_menu='btn btn-primary btn-xs';
                        $estado_menu = 'Activo';

                      }
                      ?>
                      <tr>
                        <td><strong>+ <?php echo $row_menu->strmenu; ?></strong></td>
                          <td>
                              <center>
                                  <div class="btn-group btn-group-xs">
                                      <a href="perfiladmin.php?cambiomnu=<?php echo $row_menu->intidmenuperfil; ?>&es=<?php echo $estado_menu; ?>&cod=<?php echo $codigo; ?>" role="button"   class="<?php echo $color_menu; ?>" ><strong><?php echo $estado_menu; ?></strong></a>
                                  </div>
                              </center>
                          </td>
                      </tr>
                      <?php

                      $sentencia_sub = $base_de_datos->query("SELECT a.idperfilusrfrm, b.strformulario, a.bolactivo, c.strperfil, b.strkeymenu, a.idfrm, a.idperfil
                                 FROM tblcatperfilusrfrm as a
                                 inner join tblcatformularios as b on a.idfrm = b.idfrm
                                 inner join tblcatperfilusr as c on a.idperfil = c.idperfil
                                 WHERE a.idperfil= $codigo and b.strkeymenu = '$row_menu->strtipomenu'
                                 order by a.idperfilusrfrm asc");
                       $resul_sub = $sentencia_sub->fetchAll(PDO::FETCH_OBJ);

                       $estado_sub = '';

                       $sentencia_contar = $base_de_datos->query("SELECT count(*) contar
                                  FROM tblcatperfilusrfrm as a
                                  inner join tblcatformularios as b on a.idfrm = b.idfrm
                                  inner join tblcatperfilusr as c on a.idperfil = c.idperfil
                                  WHERE a.idperfil= $codigo and b.strkeymenu = '$row_menu->strtipomenu'");
                       $cantidad_filas = $sentencia_contar->fetch(PDO::FETCH_OBJ);

                       $filas_sub = $cantidad_filas->contar;

                       if($filas_sub > 0)
                       {

                         foreach ($resul_sub as $row_sub){

                         if($row_sub->bolactivo =='0'){

                                  $color_sub  ='btn btn-danger btn-xs';
                                  $estado_sub = 'Inactivo';

                         }else{
                                  $color_sub ='btn btn-primary btn-xs';
                                  $estado_sub = 'Activo';

                                }


                        ?>
                        <tr>
                          <td><a href="perfiladmindet.php?formdet=<?php echo $row_sub->idfrm;?>&perfil=<?php echo $row_sub->idperfil;?>" >- <?php echo $row_sub->strformulario; ?></a></td>
                            <td>
                                <center>
                                    <div class="btn-group btn-group-xs">
                                        <a href="perfiladmin.php?cambio=<?php echo $row_sub->idperfilusrfrm; ?>&es=<?php echo $estado_sub; ?>&cod=<?php echo $codigo; ?>" role="button"   class="<?php echo $color_sub; ?>" ><strong><?php echo $estado_sub; ?></strong></a>
                                    </div>
                                </center>
                            </td>
                        </tr>
                      <?php } ?>
                        <?php } ?>
                        <?php } ?>
                    </table>
                        <div align="left"><a href="perfil.php" class="btn btn-info btn-sm" role="button"><i class="glyphicon glyphicon-menu-left"></i> <strong>Regresar a perfiles</strong></a></div>
                  </div>
              </div>
              <div class="tab-pane fade" id="profile">
                  <br>

              </div>
          </div>

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
