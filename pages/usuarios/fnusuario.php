<?php
require_once 'reg.php';

if(isset($_POST['fechabaja']) && (isset($_POST['idempleado'])) && (isset($_POST['estado_usuario'])) )
{
 $cambio_estado="";
 if($_POST['estado_usuario'] == '0'){  $cambio_estado = "1"; } else {  $cambio_estado = "0"; }

 baja_colaborador($_POST['fechabaja'], $_POST['idempleado'], $cambio_estado,$base_de_datos);
}


/*Lista los tipos de perfiles de accesos que se pueden registrar en la base de datos*/
function fillperfil_usuario($val,$bd)
{
  $base_de_datos = $bd;
  $sentencia = $base_de_datos->query("SELECT idperfil,strperfil FROM tblcatperfilusr where bolactivo = '1'");
  $perfiles = $sentencia->fetchAll(PDO::FETCH_OBJ);
  echo '<option value="">Seleccione</option>';
  foreach ($perfiles as $perfil )
  {
    echo '<option value="'. $perfil->idperfil .'"';

    if($perfil->idperfil==$val)
    {
          echo "selected";
    }

    echo ">". $perfil->strperfil .'</option>' . "\n";
  }
};

/*Lista los tipos de perfiles de accesos que se pueden registrar en la base de datos*/
function fillcartera_usuario($val,$bd)
{
  $base_de_datos = $bd;
  $sentencia = $base_de_datos->query("SELECT idcartera,descripcion FROM tblcatcartera where estado = true");
  $carteras = $sentencia->fetchAll(PDO::FETCH_OBJ);
  echo '<option value="">Seleccione</option>';
  foreach ($carteras as $cartera )
  {
    echo '<option value="'. $cartera->idcartera .'"';

    if($cartera->idcartera==$val)
    {
          echo "selected";
    }

    echo ">". $cartera->descripcion .'</option>' . "\n";
  }
};

/*Lista los tipos de perfiles de accesos que se pueden registrar en la base de datos*/
function fillsucursales($val,$bd)
{
  $base_de_datos = $bd;
  $sentencia = $base_de_datos->query("SELECT sucursal_id, nombre FROM sucursales");
  $sucursales = $sentencia->fetchAll(PDO::FETCH_OBJ);
  echo '<option value="">Seleccione</option>';
  foreach ($sucursales as $sucursal )
  {
    echo '<option value="'. $sucursal->sucursal_id .'"';

    if($sucursal->sucursal_id==$val)
    {
          echo "selected";
    }

    echo ">". $sucursal->nombre .'</option>' . "\n";
  }
};

/*Funcion para crear un nuevo colaborador*/
function insertar_colaborador($pnombre, $snombre, $papellido, $sapellido, $sexo,
                              $identificacion, $telefono, $correo, $password, $direccion, $perfil, $user, $cartera, $sucursal, $bd)
{
    try {
        $base_de_datos = $bd;
        $password = MD5($password);
        $usuario_creo = $_SESSION["user"];

        date_default_timezone_set('America/Managua');
        $datetime_variable = new DateTime();
        $datetime_formatted = date_format($datetime_variable, 'Y-m-d H:i:s');

        // Verificar si el correo ya existe
        $sql_check = "SELECT COUNT(*) FROM tblcatusuario WHERE strcorreo = ?";
        $sentencia_check = $base_de_datos->prepare($sql_check);
        $sentencia_check->execute([$correo]);
        $existe_correo = $sentencia_check->fetchColumn();

        if ($existe_correo > 0) {
            // Redirigir con mensaje de error si el correo ya está registrado
            header("location: usuarios.php?token=3"); // Token 3 para correo existente
            exit();
        }

        // Si el correo no existe, proceder con el insert
        $sql = "INSERT INTO tblcatusuario(
                  strpnombre, strsnombre, strpapellido, strsapellido, strsexo,
                  stridentificacion, strcontacto, strcorreo, strpassword, strdireccion, strusuariocreo, datfechacreo, intidperfil, idcartera, sucursal_id, strusuario)
                VALUES (?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $sentencia = $base_de_datos->prepare($sql);
        $sentencia->execute([$pnombre, $snombre, $papellido, $sapellido, $sexo,
                             $identificacion, $telefono, $correo, $password, $direccion, $usuario_creo, $datetime_formatted, $perfil, $cartera, $sucursal, $user]);
        
        $cmdtuplas = $sentencia->rowCount();

        if ($cmdtuplas == 1) {
            header("location: usuarios.php?token=1"); // Éxito
        } else {
            header("location: usuarios.php?token=2"); // Error en la inserción
        }
   } catch (\Exception $e) {
        header("location: usuarios.php?token=2");
    }
}


/*Funcion para editar o actualizar la informacion de un colaborador*/
function actualizar_usuario($id,$pnombre, $snombre, $papellido, $sapellido, $identificacion,
                            $correo, $telefono,$sexo,$direccion,$idperfil,$strpassword, $cartera, $sucursal, $user,  $con)
{
  try {
    /*Se llama funcion para conectar con base de datos de postgreSQl*/
    $base_de_datos = $con;

    $usuario_creo = $_SESSION["user"];

    $_SESSION["sentencia"] = "";
    $sql="";

    date_default_timezone_set('America/Managua');

    $datetime_variable = new DateTime();
    $datetime_formatted = date_format($datetime_variable, 'Y-m-d H:i');

    if((strlen($strpassword)) == 0){

      $sql = "UPDATE tblcatusuario
       SET
           strpnombre = '$pnombre',
           strsnombre = '$snombre',
           strpapellido = '$papellido',
           strsapellido = '$sapellido',
           strsexo = '$sexo',
           strcorreo = '$correo',
           stridentificacion = '$identificacion',
           strdireccion = '$direccion',
           strcontacto = '$telefono',
           strusuariomodifico = '$usuario_creo',
           datfechamodifico = current_date,
           intidperfil = $idperfil,
           strusuario = '$user',
           idcartera = COALESCE($cartera,NULL),
           sucursal_id = $sucursal
       WHERE intid =   $id";

       $_SESSION["sentencia"] = $sql;

     }else {

        $strpassword = md5($strpassword);

        $sql = "UPDATE tblcatusuario
         SET
             strpnombre = '$pnombre',
             strsnombre = '$snombre',
             strpapellido = '$papellido',
             strsapellido = '$sapellido',
             strsexo = '$sexo',
             strcorreo = '$correo',
             stridentificacion = '$identificacion',
             strdireccion = '$direccion',
             strcontacto = '$telefono',
             strusuariomodifico = '$usuario_creo',
             strpassword = '$strpassword',
             datfechamodifico = current_date,
             intidperfil = $idperfil,
             strusuario = '$user',
             idcartera = $cartera,
             sucursal_id = $sucursal
         WHERE intid = $id";

          $_SESSION["sentencia"] = $sql;
      }

      $id = base64_encode($id);

      $sentencia = $base_de_datos->prepare($sql);
      $resultado = $sentencia->execute();
      $cmdtuplas = $sentencia->rowCount();


      if ($cmdtuplas == 1) {
         /*si se actualiza correctamente se envia token para mensaje de exito*/
         header("location: usuariosedit.php?id=". $id ."&token=1");
      } else {
         /*si no se actualiza correctamente se envia token para mensaje de exito*/
         header("location: usuariosedit.php?id=". $id ."&token=2");
      }

  } catch (\Exception $e) {
         header("location: usuariosedit.php?id=". $id ."&token=2");
  }

};

/*Funcion que se manda a llamar para dar de baja a un colaborador*/
function baja_colaborador($fechabaja, $idempleado, $estado, $con)
{
try {
  $base_de_datos = $con;
  $usuario_creo = $_SESSION["user"];

  date_default_timezone_set('America/Managua');      //Don't forget this..I had used this..just didn't mention it in the post

  $datetime_variable = new DateTime();
  $datetime_formatted = date_format($datetime_variable, 'Y-m-d H:i:s');

  $sql = "UPDATE tblcatusuario
          SET  strusuariomodifico = '$usuario_creo',
	             datfechamodifico = '$datetime_formatted',
	             datfechabaja = '$fechabaja',
	             bolactivo = '$estado'
          WHERE intid = $idempleado";

  $sentencia = $base_de_datos->prepare($sql);
  $resultado = $sentencia->execute();
  $cmdtuplas = $sentencia->rowCount();

  /*Si se actualiza correctamente se lanza alert de confirmacion*/
  if($cmdtuplas == 1)
  {
    if($estado)
    {
      echo '<script>
              alert("Se activo el usuario con exito.");
              window.history.go(-1);
            </script>';
    }else {
      echo '<script>
              alert("Se desactivo el usuario con exito.");
              window.history.go(-1);
            </script>';
    }

  }
  else
  /*Si no se actualiza correctamente se lanza alert de confirmacion*/
  { echo '<script>
            alert("Lo lamentamos no se logro dar de baja, por favor verifique 1.");
            window.history.go(-1);
          </script>';}

} catch (\Exception $e) {

  echo '<script>
            alert("Lo lamentamos no se logro dar de baja, por favor verifique 2.");
            window.history.go(-1);
          </script>';
}

}

 ?>
