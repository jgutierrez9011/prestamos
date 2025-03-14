<?php
require_once '../cn.php';


/*usuario y contraseña*/
//$user= $_POST['usuario'];
//$pass= MD5($_POST['passw']);

try {
  
  if(isset($_POST['passw']))
  {
     // Obtener los datos encriptados desde el campo oculto
   $datosEncriptados = $_POST['passw'];
    
   // Decodificar los datos encriptados (asumiendo que se utilizó base64 para encriptar)
   $datosDecodificados = base64_decode($datosEncriptados);
   
   // Separar los valores individuales
   list($user, $pass) = explode('|', $datosDecodificados);
  
  
   /* INICIA: VALIDANDO SI EL USUARIO EXISTE EN EL DIRECTORIO ACTIVO DE LA EMPRESA */
  
     //desactivamos los errores por seguridad
     //error_reporting(0);
     //error_reporting(E_ALL); //activar los errores (en modo depuración)
    
     /*$servidor_LDAP = "172.26.35.220";
     $servidor_dominio = "claroni.americamovil.ca1";
     $ldap_dn = "dc=claroni.americamovil.ca1,dc=com";
     $usuario_LDAP = $user;
     $contrasena_LDAP = $pass;*/
	 
	 $servidor_LDAP = "10.70.1.11";
     $servidor_dominio = "claroni.americamovil.ca1";
     $ldap_dn = "dc=claroni.americamovil.ca1,dc=com";
     $usuario_LDAP = $user;
     $contrasena_LDAP = $pass;
    
    
     $conectado_LDAP = ldap_connect($servidor_LDAP);
     ldap_set_option($conectado_LDAP, LDAP_OPT_PROTOCOL_VERSION, 3);
     ldap_set_option($conectado_LDAP, LDAP_OPT_REFERRALS, 0);
    
     if ($conectado_LDAP) 
     {
       
       //echo "<br>Conectado correctamente al servidor LDAP " . $servidor_LDAP;
    
        //echo "<br><br>Comprobando usuario y contraseña en Servidor LDAP";
       $autenticado_LDAP = ldap_bind($conectado_LDAP, 
       $usuario_LDAP . "@" . $servidor_dominio, $contrasena_LDAP);
       if ($autenticado_LDAP)
       {
          //echo "<br>Autenticación en servidor LDAP desde Apache y PHP correcta.";
  
          $sql = "SELECT strusuario, bolactivo, strpassword FROM tblcatusuario where strusuario ='".$user."' and bolactivo = 1";
  
          $sentencia = $base_de_datos->query($sql);
          $registro_completo = $sentencia->fetchObject();
  
  
          $user_id = $registro_completo->strusuario;
  
          if($user == $user_id){
  
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
       else
       {
         echo "<br><br>No se ha podido autenticar con el servidor LDAP: " . 
           $servidor_LDAP .
           ", verifique el usuario y la contraseña introducidos";
          #No existe
          header('Location: login.php?token='.md5('$#stop#$'));
          
       }
     }
     else 
     {
       echo "<br><br>No se ha podido realizar la conexión con el servidor LDAP: " . $servidor_LDAP;
           
     }
  }
  else
  {
    header('Location: login.php?token='.md5('$#stopvar#$'));
  
  }

} catch (\Throwable $th) {
  //throw $th;
}

/* FINALIZA: VALIDANDO SI EL USUARIO EXISTE EN EL DIRECTORIO ACTIVO DE LA EMPRESA */


/* Autentificacion del usuario */
/*$sql= "SELECT strusuario, bolactivo, strpassword FROM tblcatusuario where strusuario ='".$user."' and bolactivo = 1";
$sentencia = $base_de_datos->query($sql);
$registro = $sentencia->fetchObject();

if (!$registro) {
    #No existe
    header('Location: login.php?token='.md5('$#stop#$'));
    //echo "No se encontro usuario";
    exit();
}else {

  $sql = "SELECT strusuario, bolactivo, strpassword FROM tblcatusuario where strpassword='".$pass."' and strusuario='".$user."' and bolactivo = 1";

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

             echo 1;

             //regacceso();

             /*Genera una sesion activa que dura 24 horas*/
            // if (array_key_exists('remember',$_POST))
            // {
             // Crear un nuevo cookie de sesion, que expira a los 30 días
             //ini_set('session.cookie_lifetime', 60 * 60 * 24 * 1);
             //session_regenerate_id(TRUE);
             // setcookie("COOKIE_INDEFINED_SESSION", TRUE, time()+86400);
		         // setcookie("COOKIE_DATA_INDEFINED_SESSION[nombre]", base64_encode($email), time()+86400);
             // setcookie("COOKIE_DATA_INDEFINED_SESSION[password]", base64_encode($pass), time()+86400);

             //}
             /*else
             {
	             setcookie("COOKIE_CLOSE_NAVEGADOR", TRUE, 0) . "<br/>";
             }*/

              //header("Location: inicio.php");
              //echo "inicio con exito.";

        //}else{
            //header('Location: login.php?token='.md5('$#tokens#$')."&id=".base64_encode($user_id));
            //echo "password no coincide";
             //}
   //} 

 ?>
