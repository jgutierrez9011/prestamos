<?php
require_once '../cn.php';


/*usuario y contraseña*/
//$user= $_POST['usuario'];
//$pass= MD5($_POST['passw']);

 // Obtener los datos encriptados desde el campo oculto
      $datosEncriptados = $_POST['passw'];

// CSRF protection: validar token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
    header('Location: login.php?token=csrf');
    exit();
  }
}
    //$datosEncriptados = "amhvbm55Lmd1dGllcnJlenxBZG1pbi4xMjM=";
    
   // Decodificar los datos encriptados (asumiendo que se utilizó base64 para encriptar)
   $datosDecodificados = base64_decode($datosEncriptados);
   
   // Separar los valores individuales
  list($user, $pass) = explode('|', $datosDecodificados);
  $pass_plain = $pass; // Contraseña en texto plano
  $pass_md5 = md5($pass); // Hash MD5 solo para comparar con hashes antiguos

/* FINALIZA: VALIDANDO SI EL USUARIO EXISTE EN EL DIRECTORIO ACTIVO DE LA EMPRESA */


/* Autentificacion del usuario */

$sql= "SELECT strusuario, bolactivo, strpassword, intid FROM tblcatusuario WHERE strusuario = ? AND bolactivo = true";
$sentencia = $base_de_datos->prepare($sql);
$sentencia->execute([$user]);
$registro = $sentencia->fetch(PDO::FETCH_OBJ);

if (!$registro) {
  echo "No se encontro usuario";
  header('Location: login.php?token='.md5('$#stop#$'));
  
  exit();
} else {
  $hash_bd = $registro->strpassword;
  $user_id = $registro->intid;

  // 1. Intentar login con password_hash (bcrypt)
  if (password_verify($pass_plain, $hash_bd)) {
    // Si el hash es MD5, migrar a password_hash
    if (strlen($hash_bd) === 32 && ctype_xdigit($hash_bd)) {
      $nuevoHash = password_hash($pass_plain, PASSWORD_DEFAULT);
      $sqlUpdate = "UPDATE tblcatusuario SET strpassword = ? WHERE intid = ?";
      $stmtUpdate = $base_de_datos->prepare($sqlUpdate);
      $stmtUpdate->execute([$nuevoHash, $user_id]);
    }
    // Login exitoso
    $_SESSION["time"] = time();
    $_SESSION["user"] = $user;
    globales_usuario($_SESSION["user"],$base_de_datos);

    if (array_key_exists('remember',$_POST)) {
      setcookie("COOKIE_INDEFINED_SESSION", '1', [
        'expires' => time()+86400,
        'path' => '/',
        'secure' => false, // set to true in production (HTTPS)
        'httponly' => true,
        'samesite' => 'Lax'
      ]);
      setcookie("COOKIE_DATA_INDEFINED_SESSION[nombre]", base64_encode($user), [
        'expires' => time()+86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
      ]);
      setcookie("COOKIE_DATA_INDEFINED_SESSION[password]", base64_encode($hash_bd), [
        'expires' => time()+86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
      ]);
    }
    header("Location: inicio.php");
    echo "inicio con exito.";
    exit();
  }
  // 2. Intentar login con MD5 (antiguo)
  elseif ($hash_bd === $pass_md5) {
    // Migrar a password_hash
    $nuevoHash = password_hash($pass_plain, PASSWORD_DEFAULT);
    $sqlUpdate = "UPDATE tblcatusuario SET strpassword = ? WHERE intid = ?";
    $stmtUpdate = $base_de_datos->prepare($sqlUpdate);
    $stmtUpdate->execute([$nuevoHash, $user_id]);

    // Login exitoso
    $_SESSION["time"] = time();
    $_SESSION["user"] = $user;
    globales_usuario($_SESSION["user"],$base_de_datos);

    if (array_key_exists('remember',$_POST)) {
      setcookie("COOKIE_INDEFINED_SESSION", '1', [
        'expires' => time()+86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
      ]);
      setcookie("COOKIE_DATA_INDEFINED_SESSION[nombre]", base64_encode($user), [
        'expires' => time()+86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
      ]);
      setcookie("COOKIE_DATA_INDEFINED_SESSION[password]", base64_encode($nuevoHash), [
        'expires' => time()+86400,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
      ]);
    }
    header("Location: inicio.php");
    echo "inicio con exito.";
    exit();
  } else {
    header('Location: login.php?token='.md5('$#tokens#$'));
    echo "password no coincide";
    exit();
  }
}

 ?>
