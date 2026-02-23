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

define("BASE_URL", getenv('BASE_URL') ?: "http://localhost/credimore"); // En local
// define("BASE_URL", "https://mi-dominio.com"); // En producción


/*
CADENA DE CONEXION A POSGRESQL
 */

// Railway/hosting platforms usually expose DATABASE_URL / DATABASE_PUBLIC_URL and PG* variables.
// Keep DB_* compatibility for local docker-compose usage.
function obtener_config_postgres()
{
    $databaseUrl = getenv('DATABASE_URL') ?: getenv('DATABASE_PUBLIC_URL') ?: '';

    $config = [
        'host' => getenv('DB_HOST') ?: getenv('PGHOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: getenv('PGPORT') ?: '5432',
        'name' => getenv('DB_NAME') ?: getenv('PGDATABASE') ?: 'credimore',
        'user' => getenv('DB_USER') ?: getenv('PGUSER') ?: 'postgres',
        'pass' => getenv('DB_PASS') ?: getenv('PGPASSWORD') ?: 'postgres',
        'sslmode' => getenv('DB_SSLMODE') ?: getenv('PGSSLMODE') ?: '',
    ];

    if (!empty($databaseUrl)) {
        $parsed = parse_url($databaseUrl);
        if ($parsed !== false) {
            if (!empty($parsed['host'])) {
                $config['host'] = $parsed['host'];
            }
            if (!empty($parsed['port'])) {
                $config['port'] = (string) $parsed['port'];
            }
            if (!empty($parsed['path'])) {
                $config['name'] = ltrim($parsed['path'], '/');
            }
            if (!empty($parsed['user'])) {
                $config['user'] = rawurldecode($parsed['user']);
            }
            if (array_key_exists('pass', $parsed) && $parsed['pass'] !== null) {
                $config['pass'] = rawurldecode($parsed['pass']);
            }
            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                if (!empty($queryParams['sslmode'])) {
                    $config['sslmode'] = $queryParams['sslmode'];
                }
            }
        }
    }

    // In managed PostgreSQL (e.g. Railway), SSL is commonly required.
    if (empty($config['sslmode']) && !in_array($config['host'], ['localhost', '127.0.0.1'], true)) {
        $config['sslmode'] = 'require';
    }

    return $config;
}

function construir_dsn_postgres($config)
{
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['name']}";
    if (!empty($config['sslmode'])) {
        $dsn .= ";sslmode={$config['sslmode']}";
    }

    return $dsn;
}

$dbConfig = obtener_config_postgres();

 $ruta = BASE_URL . "/pages/usuarios/inicio.php";
 
 try {
     // Cadena de conexión para PostgreSQL
     $base_de_datos = new PDO(
         construir_dsn_postgres($dbConfig),
         $dbConfig['user'],
         $dbConfig['pass']
     );
     
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
                $dbConfig = obtener_config_postgres();
                $conexion = new PDO(
                    construir_dsn_postgres($dbConfig),
                    $dbConfig['user'],
                    $dbConfig['pass']
                );
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
