<?php
require_once 'reg.php';

if(isset($_POST['fechabaja']) && (isset($_POST['idempleado'])) && (isset($_POST['estado_usuario'])) )
{
 $cambio_estado="";
 if($_POST['estado_usuario'] == '0'){  $cambio_estado = "1"; } else {  $cambio_estado = "0"; }

 baja_colaborador($_POST['fechabaja'], $_POST['idempleado'], $cambio_estado,$base_de_datos);
}

if (isset($_POST['action']) && $_POST['action'] === 'cambiar_contrasena') {
    $idUsuario = $_SESSION["idusuario"];
    $actualContrasena = $_POST['actualContrasena'] ?? '';
    $nuevaContrasena = $_POST['nuevaContrasena'] ?? '';
    $resultado = cambiarContrasena($idUsuario, $actualContrasena, $nuevaContrasena, $base_de_datos);
    echo json_encode($resultado);
    exit;
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

// Validar contraseña fuerte
function validarPasswordFuerte($password) {
    return [
        'longitud' => strlen($password) >= 8,
        'minuscula' => preg_match('/[a-z]/', $password),
        'mayuscula' => preg_match('/[A-Z]/', $password),
        'numero' => preg_match('/[0-9]/', $password),
        'especial' => preg_match('/[^A-Za-z0-9]/', $password)
    ];
}

function cambiarContrasena($idUsuario, $actualContrasena, $nuevaContrasena, $conn) {
    // Obtener el hash actual de la base de datos
    $stmt = $conn->prepare("SELECT strpassword FROM tblcatusuario WHERE intid = ?");
    $stmt->execute([$idUsuario]);
    $hashActual = $stmt->fetchColumn();

    if ($hashActual === false) {
        return ['success' => false, 'message' => 'Usuario no encontrado.'];
    }

    // Verificar la contraseña actual
    if (MD5($actualContrasena) !== $hashActual) {
        return ['success' => false, 'message' => 'La contraseña actual es incorrecta.'];
    }

        // Validar contraseña fuerte
    $criterios = validarPasswordFuerte($nuevaContrasena);
    $errores = [];

    if (!$criterios['longitud'])  $errores[] = "• Mínimo 8 caracteres";
    if (!$criterios['minuscula']) $errores[] = "• Al menos una letra minúscula";
    if (!$criterios['mayuscula']) $errores[] = "• Al menos una letra mayúscula";
    if (!$criterios['numero'])    $errores[] = "• Al menos un número";
    if (!$criterios['especial'])  $errores[] = "• Al menos un carácter especial";

    if (!empty($errores)) {
        return [
            'success' => false,
            'message' => "La nueva contraseña no cumple con los siguientes criterios:\n" . implode("\n", $errores)
        ];
    }

    // Hashear la nueva contraseña
    //$nuevoHash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
    $nuevoHash = MD5($nuevaContrasena);

    // Actualizar en la base de datos
    $stmt = $conn->prepare("UPDATE tblcatusuario SET strpassword = ? WHERE intid = ?");
    $resultado = $stmt->execute([$nuevoHash, $idUsuario]);

    if ($resultado) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'No se pudo actualizar la contraseña.'];
    }
}


/*Funcion para crear un nuevo colaborador*/
function insertar_colaborador($pnombre, $snombre, $papellido, $sapellido, $sexo,
                              $identificacion, $telefono, $correo, $password, $direccion, $perfil, $user, $cartera, $sucursal, $bd)
{
    try {
        $base_de_datos = $bd;
        $usuario_creo = $_SESSION["user"];

         // Validar contraseña segura
        $criterios = validarPasswordFuerte($password);
        if (in_array(false, $criterios, true)) {
            $errores = [];
            if (!$criterios['longitud']) $errores[] = "Debe tener al menos 8 caracteres";
            if (!$criterios['minuscula']) $errores[] = "Debe contener al menos una letra minúscula";
            if (!$criterios['mayuscula']) $errores[] = "Debe contener al menos una letra mayúscula";
            if (!$criterios['numero']) $errores[] = "Debe contener al menos un número";
            if (!$criterios['especial']) $errores[] = "Debe contener al menos un carácter especial";

            $_SESSION['errores_password'] = $errores;
            header("location: usuarios.php?token=4"); // Token 4: contraseña débil
            exit();
        }

        $password = MD5($password);

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
        // Validar contraseña fuerte
    $criterios = validarPasswordFuerte($strpassword);
    $errores = [];

    if (!$criterios['longitud'])  $errores[] = "• Mínimo 8 caracteres";
    if (!$criterios['minuscula']) $errores[] = "• Al menos una letra minúscula";
    if (!$criterios['mayuscula']) $errores[] = "• Al menos una letra mayúscula";
    if (!$criterios['numero'])    $errores[] = "• Al menos un número";
    if (!$criterios['especial'])  $errores[] = "• Al menos un carácter especial";

    if (!empty($errores)) {
      $_SESSION['errores_password'] = $errores;
            $id_encoded = base64_encode($id);
            header("location: usuariosedit.php?id=$id_encoded&token=4");
            exit();
 
    }

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
