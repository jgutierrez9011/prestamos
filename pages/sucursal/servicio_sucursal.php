<?php
require_once '../cn.php';     // conexión PDO ($pdo)
require_once 'fnsucursal.php';        // clase Sucursal

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';
$respuesta = ['success' => false, 'mensaje' => 'Acción no válida'];

try {
    $pdo = $base_de_datos;
    $sucursal = new Sucursal($pdo);  // instanciar clase con conexión

    switch ($accion) {
        case 'listar':
            $respuesta = $sucursal->listar();
            break;

        case 'insertar':
            $nombre = $_POST['nombre'] ?? '';
            $direccion = $_POST['direccion'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $fecha_apertura = $_POST['fecha_apertura'] ?? date('Y-m-d');

            $respuesta['success'] = $sucursal->insertar($nombre, $direccion, $telefono, $fecha_apertura);
            break;

        case 'editar':
            $id = $_POST['sucursal_id'] ?? 0;
            $nombre = $_POST['nombre'] ?? '';
            $direccion = $_POST['direccion'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $fecha_apertura = $_POST['fecha_apertura'] ?? date('Y-m-d');

            $respuesta['success'] = $sucursal->actualizar($id, $nombre, $direccion, $telefono, $fecha_apertura);
            break;

        case 'eliminar':
            $id = $_POST['sucursal_id'] ?? 0;
            $respuesta['success'] = $sucursal->eliminar($id);
            break;
    }

} catch (Exception $e) {
    $respuesta = [
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ];
}

echo json_encode($respuesta);
