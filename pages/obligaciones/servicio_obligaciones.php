<?php
require_once '../cn.php';
require_once 'fnobligaciones.php';

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? '';
$respuesta = ['success' => false, 'mensaje' => 'Acción no válida'];

try {
    $pdo = $base_de_datos;
    $obligacion = new ObligacionFinanciera($pdo);

    switch ($accion) {
        case 'listar':
            $respuesta = $obligacion->listar();
            break;

        case 'insertar':
            $id_solicitud = $_POST['id_solicitud'] ?? 0;
            $institucion = $_POST['institucion'] ?? '';
            $monto_inicial = $_POST['monto_inicial'] ?? 0;
            $saldo = $_POST['saldo'] ?? 0;
            $cuota = $_POST['cuota'] ?? 0;

            $respuesta['success'] = $obligacion->insertar($id_solicitud, $institucion, $monto_inicial, $saldo, $cuota);
            break;

        case 'editar':
            $id = $_POST['id_obligacion'] ?? 0;
            $id_solicitud = $_POST['id_solicitud'] ?? 0;
            $institucion = $_POST['institucion'] ?? '';
            $monto_inicial = $_POST['monto_inicial'] ?? 0;
            $saldo = $_POST['saldo'] ?? 0;
            $cuota = $_POST['cuota'] ?? 0;

            $respuesta['success'] = $obligacion->actualizar($id, $id_solicitud, $institucion, $monto_inicial, $saldo, $cuota);
            break;

        case 'eliminar':
            $id = $_POST['id_obligacion'] ?? 0;
            $respuesta['success'] = $obligacion->eliminar($id);
            break;
    }

} catch (Exception $e) {
    $respuesta = [
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ];
}

echo json_encode($respuesta);
