<?php
require_once '../cn.php';     // conexión PDO en variable $pdo
require_once 'fncartera.php';          // clase Cartera

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';
$respuesta = ['success' => false, 'mensaje' => 'Acción no válida'];

try {
    $pdo = $base_de_datos;
    $cartera = new Cartera($pdo);  // instanciar clase con conexión

    switch ($accion) {
        case 'listar':
            $respuesta = $cartera->listar();
            break;

        case 'insertar':
            $descripcion = $_POST['descripcion'] ?? '';
            $minimo = $_POST['monto_minimo'] ?? 0;
            $maximo = $_POST['monto_maximo'] ?? 0;
            $usuario = $_SESSION['nombreusuario'] ?? 'sistema';

            $respuesta['success'] = $cartera->insertar($descripcion, $minimo, $maximo, $usuario);
            break;

        case 'editar':
            $id = $_POST['idcartera'] ?? 0;
            $descripcion = $_POST['descripcion'] ?? '';
            $minimo = $_POST['monto_minimo'] ?? 0;
            $maximo = $_POST['monto_maximo'] ?? 0;
            $estado = $_POST['estado'] ?? true;
            $usuario = $_SESSION['nombreusuario'] ?? 'sistema';

            $respuesta['success'] = $cartera->actualizar($id, $descripcion, $minimo, $maximo, $estado, $usuario);
            break;

        case 'eliminar':
            $id = $_POST['idcartera'] ?? 0;
            $respuesta['success'] = $cartera->eliminar($id);
            break;
    }

} catch (Exception $e) {
    $respuesta = [
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ];
}

echo json_encode($respuesta);
