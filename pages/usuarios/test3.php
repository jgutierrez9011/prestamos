<?php
 require_once '../cn.php';
 
 // Obtener los datos encriptados desde el campo oculto
   $datosEncriptados = "amhvbm55Lmd1dGllcnJlenxBZG1pbi4xMjM=";
    
  // Decodificar los datos encriptados (asumiendo que se utilizó base64 para encriptar)
   $datosDecodificados = base64_decode($datosEncriptados);
   
   
   // Separar los valores individuales
   list($user, $pass) = explode('|', $datosDecodificados);
   
   echo "El usuario es:".$user."\n";
   echo "El password es:".MD5($pass)."\n";


?>
