<?php
/* Inicia nueva sesion */
require_once '../cn.php';

// Asegurar que la sesión esté iniciada (cn.php normalmente la inicia)
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Revisa si la variable de session del usuario se ha inicializado
if (!isset($_SESSION['user']) || $_SESSION['user'] === '') {
	// Si la variable de session del usuario es igual a vacio o no existe, redirige al login
	header('Location: inicio.php');
	exit;
}
?>
