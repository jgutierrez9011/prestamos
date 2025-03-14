<?php
/* Inicia nueva sesion */
require_once '../cn.php';

	//Revisa si la variable de session del usuario se ha inicializado
	if((strlen($_SESSION["user"]) == 0) and ($_SESSION["user"]=="")){
	//Si la variable de session del usuario es igual a vacio y no se ha creado entonces lo manda al login.php
		header("location:inicio.php");
		}
?>
