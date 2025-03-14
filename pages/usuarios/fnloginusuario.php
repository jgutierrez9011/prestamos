<?php
require_once '../cn.php';


/*usuario y contraseña*/
//$user= $_POST['usuario'];
//$pass= MD5($_POST['passw']);

 // Obtener los datos encriptados desde el campo oculto
      $datosEncriptados = $_POST['passw'];
    //$datosEncriptados = "amhvbm55Lmd1dGllcnJlenxBZG1pbi4xMjM=";
    
   // Decodificar los datos encriptados (asumiendo que se utilizó base64 para encriptar)
   $datosDecodificados = base64_decode($datosEncriptados);
   
   // Separar los valores individuales
   list($user, $pass) = explode('|', $datosDecodificados);
   
   $pass = md5($pass);
   

/* FINALIZA: VALIDANDO SI EL USUARIO EXISTE EN EL DIRECTORIO ACTIVO DE LA EMPRESA */


/* Autentificacion del usuario */
$sql= "SELECT strusuario, bolactivo, strpassword FROM tblcatusuario where strusuario ='".$user."' and bolactivo = true";
$sentencia = $base_de_datos->query($sql);
$registro = $sentencia->fetchObject();

if (!$registro) {
    #No existe
    header('Location: login.php?token='.md5('$#stop#$'));
    echo "No se encontro usuario";
    exit();
}else {

  $sql = "SELECT strusuario, bolactivo, strpassword FROM tblcatusuario where strpassword='".$pass."' and strusuario='".$user."' and bolactivo = true";

  $sentencia = $base_de_datos->query($sql);
  $registro_completo = $sentencia->fetchObject();



  $user_id = $registro_completo->strpassword;


        if($pass == $user_id){

             //hora del inicio de sesion
             $_SESSION["time"] = time();
             //usuario que inicio sesion
             $_SESSION["user"] = $user;
             //globales de usuario
             globales_usuario($_SESSION["user"],$base_de_datos);

             //echo 1;

             //regacceso();

             /*Genera una sesion activa que dura 24 horas*/
					 if (array_key_exists('remember',$_POST))
					 {
						  // Crear un nuevo cookie de sesion, que expira a los 30 días
                    //ini_set('session.cookie_lifetime', 60 * 60 * 24 * 1);
                    //session_regenerate_id(TRUE);
                    setcookie("COOKIE_INDEFINED_SESSION", TRUE, time()+86400);
                    setcookie("COOKIE_DATA_INDEFINED_SESSION[nombre]", base64_encode($email), time()+86400);
                    setcookie("COOKIE_DATA_INDEFINED_SESSION[password]", base64_encode($pass), time()+86400);

					 }
					 /*else
					 {
						 setcookie("COOKIE_CLOSE_NAVEGADOR", TRUE, 0) . "<br/>";
					 }*/

              header("Location: inicio.php");
              echo "inicio con exito.";

        }else{
            header('Location: login.php?token='.md5('$#tokens#$')."&id=".base64_encode($user_id));
            echo "password no coincide";
             }
   } 

 ?>
