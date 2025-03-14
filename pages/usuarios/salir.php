<?php
//Destruye la session del usuario y lo redireccina a index.php
include 'sesion.php';

$user = $_SESSION["user"];

if (isset($_COOKIE['COOKIE_INDEFINED_SESSION'])) {

   setcookie("COOKIE_INDEFINED_SESSION", TRUE, time()-86400);
   setcookie("COOKIE_DATA_INDEFINED_SESSION[nombre]", $email, time()-86400);
   setcookie("COOKIE_DATA_INDEFINED_SESSION[password]", $pass, time()-86400);

    unset($_COOKIE['COOKIE_INDEFINED_SESSION']);
    unset($_COOKIE['COOKIE_DATA_INDEFINED_SESSION[nombre]']);
    unset($_COOKIE['COOKIE_DATA_INDEFINED_SESSION[password]']);

    setcookie('COOKIE_INDEFINED_SESSION', null, -1, '/');
    setcookie('COOKIE_DATA_INDEFINED_SESSION[nombre]', null, -1, '/');
    setcookie('COOKIE_DATA_INDEFINED_SESSION[password]', null, -1, '/');

}

session_destroy();
header("Location: login.php");
?>
