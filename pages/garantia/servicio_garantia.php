<?php
require_once '../cn.php';     // conexión PDO ($pdo)
require_once 'fngarantia.php';        // clase Garantia

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';
$id_solicitud = $_POST['id_solicitud'] ?? '';
$respuesta = ['success' => false, 'mensaje' => 'Acción no válida'];

try {
    $pdo = $base_de_datos;
    $garantia = new Garantia($pdo);  // instanciar clase con conexión

    switch ($accion) {
        case 'listar':
            $respuesta = $garantia->listar();
            break;

        case 'listarporid':
            $respuesta = $garantia->obtenerPorId($id_solicitud);
            break;

        case 'insertar':
            $id_solicitud = $_POST['id_solicitud'] ?? 0;
            $descripcion = $_POST['descripcion'] ?? '';
            $cantidad = $_POST['cantidad'] ?? 1;
            $marca = $_POST['marca'] ?? '';
            $color = $_POST['color'] ?? '';
            $ubicacion = $_POST['ubicacion'] ?? '';
            $valor_realizacion = $_POST['valor_realizacion'] ?? 0;

            $respuesta['success'] = $garantia->insertar($id_solicitud, $descripcion, $cantidad, $marca, $color, $ubicacion, $valor_realizacion);
            break;

        case 'editar':
            $id = $_POST['id_garantia'] ?? 0;
            $id_solicitud = $_POST['id_solicitud'] ?? 0;
            $descripcion = $_POST['descripcion'] ?? '';
            $cantidad = $_POST['cantidad'] ?? 1;
            $marca = $_POST['marca'] ?? '';
            $color = $_POST['color'] ?? '';
            $ubicacion = $_POST['ubicacion'] ?? '';
            $valor_realizacion = $_POST['valor_realizacion'] ?? 0;

            $respuesta['success'] = $garantia->actualizar($id, $id_solicitud, $descripcion, $cantidad, $marca, $color, $ubicacion, $valor_realizacion);
            break;

        case 'eliminar':
            $id = $_POST['id_garantia'] ?? 0;
            $respuesta['success'] = $garantia->eliminar($id);
            break;
    }

} catch (Exception $e) {
    $respuesta = [
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ];
}

echo json_encode($respuesta);
