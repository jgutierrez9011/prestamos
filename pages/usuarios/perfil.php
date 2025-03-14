<?php
require_once  'reg.php';

function limpiar($tags){
  $tags = trim($tags);
  return $tags;
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
            <h1>Catálogo de perfiles</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Control de usuarios</a></li>
              <li class="breadcrumb-item active">Catálogo de perfiles</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>



    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Lista de perfiles de usuario</h3>

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

          <div class="row ">
            <a href="#new" role="button" class="btn btn-primary" data-toggle="modal"><strong>Crear nuevo perfil</strong></a>
          </div>

          <br>

          <?php

          /*if(!empty($_POST['nombre']))
              {
                      $conexion = conexion_bd(1);
                      $nombre=limpiar($_POST['nombre']);
                      if(!empty($_POST['id'])){
                        $id=limpiar($_POST['id']);
                        pg_query($conexion," UPDATE tblcatperfilusr SET strperfil='$nombre'  WHERE idperfil = $id ");
                        echo mensajes("El perfil ha sido actualizado con exito","verde");
                      }else{

                        pg_query($conexion,"INSERT INTO tblcatperfilusr(strperfil, bolactivo) VALUES ('$nombre', 'True');");

                        $sql=pg_query($conexion,"SELECT MAX(idperfil) as idperfil FROM tblcatperfilusr");
                        if($row=pg_fetch_array($sql)){	$id_perfil=$row['idperfil'];		}

                        $sql=pg_query($conexion,"SELECT idfrm, strformulario, strnombreform, bolestado FROM tblcatformularios;");

                        while($row=pg_fetch_row($sql)){
                          $id_formulario=$row[0];
                          pg_query($conexion,"INSERT INTO tblcatperfilusrfrm (idfrm, idperfil, bolactivo) VALUES ( $id_formulario, $id_perfil, 'False')");
                        }

                        $sql2=pg_query($conexion,"SELECT intidmenu, strmenu, strtipomenu, strnivelmenu, bolactivo FROM tblcatmenu;");
                        while($row=pg_fetch_row($sql2)){
                          $id_menu=$row[0];
                          pg_query($conexion,"INSERT INTO tblcatmenuperfil(idperfil, intidmenu, bolactivo) VALUES ( $id_perfil, $id_menu, 'False')");
                        }

                        $sql3=pg_query($conexion,"SELECT idfrmdetalle, idfrm, strnombreelemento, strtipotag, bolestado from tblcatformulariodetalle order by idfrmdetalle asc, idfrm asc;");
                        while($row=pg_fetch_row($sql3)){
                          $id_frmdet=$row[0];
                          pg_query($conexion,"INSERT INTO tblcatperfilusrfrmdetalle(idfrmdetalle, idperfil, bolactivo) VALUES ( $id_frmdet, $id_perfil, 'False')");
                        }

                        echo mensajes("El perfil ha sido registrado con exito","verde");
                      }
              } */
           ?>

           <div class="row table-responsive">
             <table class="table table-bordered">
               <tr class="well">
                   <td><strong>Descripcion de perfil</strong></td>
                     <td width="20%"></td>
                 </tr>
                 <?php

                $sentencia = $base_de_datos->query("SELECT idperfil, strperfil, bolactivo FROM tblcatperfilusr;");
                $perfiles = $sentencia->fetchAll(PDO::FETCH_OBJ);

              foreach($perfiles as $perfil){
               $nn=0;
               $url= $perfil->idperfil;
               $sentencia = $base_de_datos->query("SELECT a.idperfilusrfrm, b.strformulario, a.bolactivo
                                                   FROM tblcatperfilusrfrm as a
                                                   inner join tblcatformularios as b on a.idfrm = b.idfrm
                                                   WHERE a.idperfil= $row[0] and a.bolactivo ='1';");
               $perfiles_usuarios = $sentencia->fetchAll(PDO::FETCH_OBJ);
               foreach($perfiles_usuarios as $perfiles_usuario){
                 $nn++;
               }

               if($nn==0){
                 $color='btn btn-danger btn-xs';
               }else{
                 $color='btn btn-primary btn-xs';
               }
           ?>
                 <tr>
                   <td><?php echo $perfil->strperfil; ?></td>
                     <td>
                         <center>
                             <div class="btn-group btn-group-xs">
                                 <a data-target="#m<?php echo $perfil->idperfil; ?>" role="button"   class="btn btn-default btn-xs" data-toggle="modal"><i class="fa fa-edit"></i> <strong>Editar Perfil</strong></a>
                                 <a href="perfiladmin.php?id=<?php echo $url; ?>" class="<?php echo $color; ?>"><i class="fa fa-list-ul"></i> <strong>Admin</strong></a>
                             </div>
                         </center>
                     </td>
                 </tr>

                 <!-- Modal -->
                 <div id="m<?php echo $perfil->idperfil; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-md">
                     <div class="modal-content">
                             <div class="modal-header">
                               <h4 class="modal-title">Actualizar Perfil</h4>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                 <span aria-hidden="true">&times;</span>
                               </button>
                             </div>
                      <div class="modal-body">

                        <form name="foms" action="" method="post">
                          <div class="form-group">
                            <strong>Descripcion del perfil</strong><br>
                              <input type="hidden" name="id" class="form-control" value="<?php echo $perfil->strperfil; ?>">
                              <input type="text" name="nombre" class="form-control" autocomplete="off" required value="<?php echo $perfil->strperfil; ?>">
                           </div>
                          <div class="modal-footer">
                              <button class="btn" data-dismiss="modal" aria-hidden="true"><strong>Cerrar</strong></button>
                              <button type="submit" class="btn btn-primary"><strong>Actualizar</strong></button>
                          </div>
                        </form>

                      </div>

                     </div>
                   </div>
                 </div>

                 <?php } ?>
             </table>

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
  <!-- Modal -->
 <div id="new" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-md">
      <div class="modal-content">

         <div class="modal-header">
           <h4 class="modal-title">Registrar nuevo perfil</h4>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>

         <div class="modal-body">
           <form name="xx" action="" method="post">
             <div class="form-group">
               <strong>Descripcion del perfil</strong><br>
               <input type="text" name="nombre" class="form-control" autocomplete="off" required value="">
             </div>
           <div class="modal-footer">
               <button class="btn" data-dismiss="modal" aria-hidden="true"><strong>Cerrar</strong></button>
               <button type="submit" class="btn btn-primary"><strong>Registrar</strong></button>
           </div>
           </form>
         </div>
      </div>
   </div>
 </div>
 <?php


 if(!empty($_POST['nombre']))
     {

             $nombre=limpiar($_POST['nombre']);
             if(!empty($_POST['id'])){
               $id=limpiar($_POST['id']);

               $sentencia = $base_de_datos->prepare("UPDATE tblcatperfilusr SET strperfil='$nombre'  WHERE idperfil = $id");
               $resultado = $sentencia->execute([$nombre, $edad, $id]);

               echo mensajes("El perfil ha sido actualizado con exito","verde");

             }else{

               $sentencia = $base_de_datos->prepare("INSERT INTO tblcatperfilusr(strperfil, bolactivo) VALUES ('$nombre', 'True');");
               $resultado = $sentencia->execute();

               $sql="SELECT MAX(idperfil) as idperfil FROM tblcatperfilusr";

               if( $row_result=$base_de_datos->query($sql))
               {
               $row = $row_result->fetch(PDO::FETCH_NUM);
               $id_perfil=$row[0];
               }

               $sentencia = $base_de_datos->query("SELECT idfrm, strformulario, strnombreform, bolestado FROM tblcatformularios;");
               $formularios = $sentencia->fetchAll(PDO::FETCH_OBJ);

               foreach($formularios as $row){
                 $id_formulario=$row->idfrm;

                 $sentencia = $base_de_datos->prepare("INSERT INTO tblcatperfilusrfrm (idfrm, idperfil, bolactivo) VALUES ( $id_formulario, $id_perfil, 'False')");
                 $sentencia->execute();
               }

               $sentencia = $base_de_datos->query("SELECT intidmenu, strmenu, strtipomenu, strnivelmenu, bolactivo FROM tblcatmenu;");
               $menus = $sentencia->fetchAll(PDO::FETCH_OBJ);


               foreach($menus as $row){
                 $id_menu=$row->intidmenu;
                 $sentencia = $base_de_datos->prepare("INSERT INTO tblcatmenuperfil(idperfil, intidmenu, bolactivo) VALUES ( $id_perfil, $id_menu, 'False')");
                 $sentencia->execute();

               }

               $sentencia = $base_de_datos->query("SELECT idfrmdetalle, idfrm, strnombreelemento, strtipotag, bolestado from tblcatformulariodetalle order by idfrmdetalle asc, idfrm asc;");
               $formulario_detalle = $sentencia->fetchAll(PDO::FETCH_OBJ);

               foreach($formulario_detalle as $row){
                 $id_frmdet=$row->idfrmdetalle;
                 $sentencia = $base_de_datos->prepare("INSERT INTO tblcatperfilusrfrmdetalle(idfrmdetalle, idperfil, bolactivo) VALUES ( $id_frmdet, $id_perfil, 'False')");
                 $sentencia->execute();
               }

               echo mensajes("El perfil ha sido registrado con exito","verde");
             }
     }
  ?>

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
