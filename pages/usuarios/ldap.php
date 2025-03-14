<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Validación en servidor LDAP con PHP</title>
</head>
<body>
 
<?php
  //desactivamos los errores por seguridad
  error_reporting(0);
  //error_reporting(E_ALL); //activar los errores (en modo depuración)
 
  $servidor_LDAP = "172.26.35.220";
  $servidor_dominio = "claroni.americamovil.ca1";
  $ldap_dn = "dc=claroni.americamovil.ca1,dc=com";
  $usuario_LDAP = "jhonny.gutierrez";
  $contrasena_LDAP = "Jgmar2023**";
 
  echo "<h3>Validar en servidor LDAP desde PHP</h3>";
  echo "Conectando con servidor LDAP desde PHP...";
 
  $conectado_LDAP = ldap_connect($servidor_LDAP);
  ldap_set_option($conectado_LDAP, LDAP_OPT_PROTOCOL_VERSION, 3);
  ldap_set_option($conectado_LDAP, LDAP_OPT_REFERRALS, 0);
 
  if ($conectado_LDAP) 
  {
    
    echo "<br>Conectado correctamente al servidor LDAP " . $servidor_LDAP;
 
	   echo "<br><br>Comprobando usuario y contraseña en Servidor LDAP";
    $autenticado_LDAP = ldap_bind($conectado_LDAP, 
	       $usuario_LDAP . "@" . $servidor_dominio, $contrasena_LDAP);
    if ($autenticado_LDAP)
    {
	     echo "<br>Autenticación en servidor LDAP desde Apache y PHP correcta.";
           
         /*$filter = "(sAMAccountName=".$usuario_LDAP.")";
         $result = ldap_search($conectado_LDAP, $ldap_dn, $filter);
         $entries = ldap_get_entries($conectado_LDAP, $result);
         
         if ($entries['count'] > 0) {
            // El usuario existe en el Directorio Activo, obtener su información
            $user_info = $entries[0];
            echo "Nombre completo: " . $user_info['displayname'][0] . "<br>";
            echo "Correo electrónico: " . $user_info['mail'][0] . "<br>";
            // etc.
          } else {
            // El usuario no existe en el Directorio Activo
            echo "El usuario no existe.";
          }*/


	   }
    else
    {
      echo "<br><br>No se ha podido autenticar con el servidor LDAP: " . 
	      $servidor_LDAP .
	      ", verifique el usuario y la contraseña introducidos";
    }
  }
  else 
  {
    echo "<br><br>No se ha podido realizar la conexión con el servidor LDAP: " .
        $servidor_LDAP;
  }


  // Cifrado
$data = 'Jgmar2023**';
$key = 'encuentrame';
$iv = openssl_random_pseudo_bytes(16); // Vector de inicialización aleatorio
$cipherText = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
$encryptedData = base64_encode($iv . $cipherText);

echo "<br>"."\n\r"."Encriptado :".$encryptedData;

// Descifrado
$encryptedData = base64_decode($encryptedData);
$iv = substr($encryptedData, 0, 16);
$cipherText = substr($encryptedData, 16);
$plainText = openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

echo "<br>"."\n\r"."Descencriptado :".$plainText; // Muestra "Hola mundo!"


?>
 
</body>
</html>