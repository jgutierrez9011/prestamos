<?php

$sftp_server = "192.168.8.201";
$sftp_user = "sai";
$sftp_pass = "\$JuA1u20.21";

// conectarse al servidor SFTP
$connection = ssh2_connect($sftp_server, 22);
if (ssh2_auth_password($connection, $sftp_user, $sftp_pass)) {
  echo "Conexión exitosa";
} else {
  die("No se pudo autenticar");
}

// abrir un canal SFTP
//$sftp = ssh2_sftp($connection);

// descargar un archivo
//$remote_file = "/path/to/remote/file.txt";
//$local_file = "file.txt";
//$stream = fopen("ssh2.sftp://$sftp$remote_file", 'r');
//file_put_contents($local_file, $stream);

// cerrar la conexión
//fclose($stream);
unset($connection);

?>
