<?php
// Incluir la extensión OCI8 en PHP
//extension=php_oci8_12c.dll

// Definir los detalles de la conexión
$host = "192.168.8.201:3871";
$dbname = "AINP";
$username = "TRAFICO";
$password = "AdminAI_37";

 
 
 // Obtener los datos encriptados desde el campo oculto
   $datosEncriptados = "amhvbm55Lmd1dGllcnJlenxBZG1pbi4xMjM=";
    
   // Decodificar los datos encriptados (asumiendo que se utilizó base64 para encriptar)
   $datosDecodificados = base64_decode($datosEncriptados);
   
   // Separar los valores individuales
   list($user, $pass) = explode('|', $datosDecodificados);
   
   echo "El usuario es:"$user."\n";
   echo "El password es:"$pass."\n";

// Crear una conexión a la base de datos Oracle
$conn = oci_connect($username, $password, $host . '/' . $dbname);

// Verificar si la conexión fue exitosa
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

// Realizar una consulta a la base de datos
$query = "SELECT * FROM dual";
$stid = oci_parse($conn, $query);
oci_execute($stid);

// Recorrer los resultados de la consulta
while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
    print_r($row);
}

// Cerrar la conexión a la base de datos
oci_close($conn);
?>
