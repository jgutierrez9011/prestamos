<?php

// Iniciar sesión (si no está iniciada) y asegurar cookie de sesión con HttpOnly y SameSite
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (session_id()) {
    // Reescribe la cookie de sesión para añadir HttpOnly y SameSite (no forzamos Secure en staging)
    setcookie(session_name(), session_id(), [
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => false
    ]);
}

// Avoid disclosing PHP warnings/errors to clients; log them instead
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Security headers (safe defaults for staging)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
// Report-only CSP to avoid breaking UI in staging while gathering violations
header("Content-Security-Policy-Report-Only: default-src 'self'; object-src 'none'; frame-ancestors 'none';");

// Ensure a CSRF token exists for form protection
if (empty($_SESSION['csrf'])) {
    try {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        // Fallback if random_bytes is unavailable
        $_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

define("BASE_URL", "http://localhost/credimore"); // En local
// define("BASE_URL", "https://mi-dominio.com"); // En producción


/*
CADENA DE CONEXION A POSGRESQL
 */

 $host = "localhost"; // Cambia esto por la IP o nombre de tu servidor
 $port = "5400"; // Puerto por defecto de PostgreSQL
 $nombreBaseDeDatos = "credimore";
 $usuario = "postgres";
 $pass = "posgres";

 $ruta = "/credimore/pages/usuarios/inicio.php";
 
 try {
     // Cadena de conexión para PostgreSQL
     $base_de_datos = new PDO("pgsql:host=$host;port=$port;dbname=$nombreBaseDeDatos", $usuario, $pass);
     
     // Configurar PDO para lanzar excepciones en caso de error
     $base_de_datos->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
     //echo "Conexión exitosa a PostgreSQL";
} catch (PDOException $e) {
    error_log('DB connection error: ' . $e->getMessage());
    // Do not disclose DB errors to the client; show a generic message if needed
    // echo "Ocurrió un error con la base de datos.";
    $base_de_datos = null;
}

/*FUNCION PARA CONECTAR CON BASE DE DATOS*/
function conexion_bd($bd)
{
    $conexion = ""; // Retorna la conexión con la base de datos seleccionada

    try {
        switch ($bd) {
            case 1:
                /* CADENA DE CONEXIÓN PARA MICROSOFT SQL SERVER */
                $conexion = new PDO("sqlsrv:server=MA-FON-008-D023\\SQLEXPRESS;database=analitica", "as", "Admin.123");
                break;
            
            case 2:
                /* CADENA DE CONEXIÓN CON ORACLE */
                $conexion = new PDO("oci:dbname=192.168.8.201:3871/AINP", "TRAFICO", "AdminAI_37");
                break;
            
            case 3:
                /* CADENA DE CONEXIÓN CON POSTGRESQL */
                $conexion = new PDO("pgsql:host=localhost;port=5400;dbname=credimore", "postgres", "posgres");
                break;

            default:
                throw new Exception("Base de datos no soportada");
        }
        
        // Configurar el manejo de errores
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log('conexion_bd error: ' . $e->getMessage());
        $conexion = null;
    }

    return $conexion;
}

function globales_usuario($val,$base)
{
  $usuario = $val;
  $base_de_datos = $base;
  /*se consulta el id de cargo del usuario logueado*/
  $sql="SELECT a.intid, concat(a.strpnombre,' ',a.strsnombre,' ',a.strpapellido,'  ',a.strsapellido) nombre,
        a.strusuario, a.strcorreo, b.strperfil, a.idcartera
        FROM tblcatusuario  a inner join tblcatperfilusr b on a.intidperfil = b.idperfil
        where a.strusuario = '$usuario' and a.bolactivo = true";

  $resultado = $base_de_datos->query($sql);
  $row_g = $resultado->fetch(PDO::FETCH_NUM);

  $_SESSION["idusuario"] = $row_g[0];
  $_SESSION["nombreusuario"] = $row_g[1];
  $_SESSION["correousuario"] = $row_g[3];
  $_SESSION["perfilusuario"] = $row_g[4];
  $_SESSION["carterausuario"] = $row_g[5];


  //echo $_SESSION["nombreusuario"];
}

if  (isset($_COOKIE['COOKIE_INDEFINED_SESSION']) && empty($_SESSION["idusuario"]))
{
  if ($_COOKIE['COOKIE_INDEFINED_SESSION'])
  {

    $nombre_user = base64_decode($_COOKIE['COOKIE_DATA_INDEFINED_SESSION']['nombre']);
    $password_user = base64_decode($_COOKIE['COOKIE_DATA_INDEFINED_SESSION']['password']);

    $_SESSION["user"] = $nombre_user;
    //Verifica el numero de filas
    $sql = "SELECT COUNT(*) FROM tblcatusuario where strusuario='".$nombre_user."' and bolactivo = '1'";
    if ($resultado = $base_de_datos->query($sql)) {

      $row = $resultado->fetch(PDO::FETCH_NUM);

      if ($row[0] > 0)
      {
         $sql= "SELECT strpassword FROM tblcatusuario where strpassword='".$password_user."' and strusuario='".$nombre_user."' and bolactivo = '1'";
         $resultado = $base_de_datos->query($sql);
        //Obtiene las filas que retorna la consulta
         $row = $resultado->fetch(PDO::FETCH_NUM);
         $user_id = $row["strpassword"];

          if($password_user == $user_id)
          {
             globales_usuario($_SESSION["user"],$base_de_datos);
             header("Location:inicio.php"); //envias al usuario a home.php si se lo encontro en la BD!
          }
        }
    }


  }
}

 ?>
