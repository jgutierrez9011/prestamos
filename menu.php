<?php
require_once '../cn.php';
require_once 'encabezado.php';

if (!empty($_SESSION["user"]))
{

$usersac = $_SESSION["user"];

$menu = "<nav class='main-header navbar navbar-expand navbar-white navbar-light'>

  <ul class='navbar-nav'>
    <li class='nav-item'>
      <a class='nav-link' data-widget='pushmenu' href='#' role='button'><i class='fas fa-bars'></i></a>
    </li>
  </ul>



  <ul class='navbar-nav ml-auto'>


    <li class='nav-item dropdown'>
      <a class='nav-link' data-toggle='dropdown' href='#'>
        <i class='far fa-user fa-fw'></i><i class='fa fa-caret-down'></i>
      </a>
      <div class='dropdown-menu dropdown-menu-lg dropdown-menu-right'>

        <div class='dropdown-divider'></div>
        <a href='#' onClick='return salir()' class='dropdown-item'>
          <i class='fas fa-sign-out mr-2'></i> Cerrar sesión
          <span class='float-right text-muted text-sm'></span>
        </a>
        <div class='dropdown-divider'></div>

      </div>
    </li>

  </ul>

</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class='main-sidebar sidebar-dark-primary elevation-4'>
    <!-- Brand Logo -->
  <a href='#' class='brand-link'>
  <!--  <img src='../../dist/img/AdminLTELogo.png' alt='AdminLTE Logo' class='brand-image img-circle elevation-3' style='opacity: .8'> -->
    <span class='brand-text font-weight-light'>CREDIMORE</span>
  </a>

<!-- Sidebar -->
  <div class='sidebar'>
  <!-- Sidebar user (optional) -->
<br>
    <!-- SidebarSearch Form -->
    <div class='form-inline'>
      <div class='input-group' data-widget='sidebar-search'>
        <input class='form-control form-control-sidebar' type='search' placeholder='Search' aria-label='Search'>
        <div class='input-group-append'>
          <button class='btn btn-sidebar'>
            <i class='fas fa-search fa-fw'></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class='mt-2'>
      <ul class='nav nav-pills nav-sidebar flex-column' data-widget='treeview' role='menu' data-accordion='false'>";


      $sql = "SELECT COUNT(*)
                  FROM tblcatusuario as a
                  inner join tblcatmenuperfil as b on a.intidperfil = b.idperfil
                  inner join tblcatmenu as c on b.intidmenu = c.intidmenu
                  where a.strusuario = '$usersac' and b.bolactivo = '1' and a.bolactivo = '1' and c.strnivelmenu = '1'
                  ";

      if ($resultado = $base_de_datos->query($sql)) {

        $row = $resultado->fetch(PDO::FETCH_NUM);
        $filas_menu = $row[0];

          /* Comprobar el número de filas de la sentencia SELECT */
        if ($filas_menu > 0) {


              /* Ejecutar la sentencia SELECT y trabajar con los resultados */
               $sql = "SELECT b.idperfil, c.strtipomenu, c.strmenu, c.strhref, c.strclassicono
                           FROM tblcatusuario as a
                           inner join tblcatmenuperfil as b on a.intidperfil = b.idperfil
                           inner join tblcatmenu as c on b.intidmenu = c.intidmenu
                           where a.strusuario = '$usersac' and b.bolactivo = '1' and a.bolactivo = '1' and c.strnivelmenu = '1'
                           order by c.intidmenu asc";

             foreach ($base_de_datos->query($sql) as $fila) {

                 $nombre_menu = $fila['strmenu']; $icono_menu = $fila['strclassicono'];
                 $tipomenu = $fila['strtipomenu']; $idperfil = $fila['idperfil'];

                 $menu = $menu . "<li class='nav-item'>
                                  <a href='#' class='nav-link'>
                                  <i class='nav-icon fas $icono_menu'></i>
                                  <p>
                                  $nombre_menu
                                  <i class='right fas fa-angle-left'></i>
                                  </p>
                                 </a>";


                     $sqlsubmenu = "SELECT COUNT(*)
                                    FROM tblcatperfilusrfrm as a
                                    inner join tblcatformularios as b on a.idfrm = b.idfrm
                                    inner join tblcatperfilusr as c on a.idperfil = c.idperfil
                                    WHERE a.idperfil= $idperfil and b.strkeymenu = '$tipomenu' and a.bolactivo = '1'
                                    ";

                     if ($resultado_submenu = $base_de_datos->query($sqlsubmenu)) {

                       $row_submenu = $resultado_submenu->fetch(PDO::FETCH_NUM);
                       $num_filas_submenu = $row_submenu[0];

                         /* Comprobar el número de filas de la sentencia SELECT */
                       if ($row_submenu[0] > 0) {



                             /* Ejecutar la sentencia SELECT y trabajar con los resultados */
                              $sql = "SELECT a.idperfilusrfrm, b.strformulario, b.strnombreform, a.bolactivo, c.strperfil, b.strkeymenu
                                             FROM tblcatperfilusrfrm as a
                                             inner join tblcatformularios as b on a.idfrm = b.idfrm
                                             inner join tblcatperfilusr as c on a.idperfil = c.idperfil
                                             WHERE a.idperfil= $idperfil and b.strkeymenu = '$tipomenu' and a.bolactivo = '1'
                                             order by a.idperfilusrfrm asc";

                              $menu = $menu."<ul class='nav nav-treeview' $tipomenu-$num_filas_submenu-$sql>";

                            foreach ($base_de_datos->query($sql) as $fila_submenu) {

                                $href_submenu = $fila_submenu['strnombreform'];
                                $nombre_submenu = $fila_submenu['strformulario'];

                                $num_filas_submenu = $num_filas_submenu - 1;


                                $menu = $menu."<li class='nav-item'>
                                               <a href='$href_submenu' class='nav-link'>
                                               <i class='far fa-dot-circle nav-icon'></i>
                                               <p>$nombre_submenu</p>
                                               </a>
                                               </li>";

                                               if($num_filas_submenu == 0)
                                                        {$menu = $menu."</ul>
                                                                           <!-- /.nav-second-level -->
                                                                        ";
                                                        }
                              }

                         }
                         /* SI NO HAY UN SUB MENU SE CIERRA EL MENU PRINCIPAL */
                       else {
                         $menu = $menu."</li>";
                         }
                   }



                 $filas_menu = $filas_menu - 1;
               }

               if($filas_menu == 0)
                        {$menu = $menu."</ul>
                      </nav>

                    </div>

                  </aside>";
                        }

          }
          /* No coincide ningua fila -- hacer algo en consecuencia */
        else {
            $menu = $menu . "</ul>
          </nav>

        </div>

      </aside>";
          }
      }


echo $menu;
}
?>
