<?php
header("Content-Type: application/json");
require_once '../cn.php';
require_once 'solicitud_service.php';

try {
    $pdo = $base_de_datos;
    $solicitudBL = new SolicitudPrestamo($pdo);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id_solicitud'])) {
            $id_solicitud = $_GET['id_solicitud'];
            //Cambia el estado de la solicitud a En revision
            //$estadoSolicitud = $solicitudBL->updateSolicitudEstado($_SESSION["idusuario"],2,$id_solicitud);
            $solicitud = $solicitudBL->getSolicitud($id_solicitud);
            echo json_encode($solicitud);
        } else {
            $solicitudes = $solicitudBL->getAllSolicitudes();
            echo json_encode($solicitudes);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents(filename: "php://input"), true);
        $response = $solicitudBL->createSolicitud($data);
        echo json_encode($response);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $response = $solicitudBL->updateSolicitud($data);
        echo json_encode($response);
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
?>