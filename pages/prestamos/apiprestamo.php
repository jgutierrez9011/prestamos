<?php
// api/prestamo.php

header("Content-Type: application/json");
require_once '../cn.php';
require_once 'prestamo_service.php';
require_once 'solicitud_service.php';

// Crear una instancia del servicio
$prestamoService = new PrestamoService($base_de_datos);
$PrestamoSolicitud = new SolicitudPrestamo($base_de_datos);

// Obtener la solicitud HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Manejar la solicitud según el método HTTP
switch ($method) {
    case 'GET':
        // Obtener un préstamo por ID o listar todos
        if (isset($_GET['id_prestamo'])) {
            $id_prestamo = $_GET['id_prestamo'];
            $prestamo = $prestamoService->getPrestamo($id_prestamo);
            if ($prestamo) {
                echo json_encode($prestamo);
            } else {
                http_response_code(404); // No encontrado
                echo json_encode(["message" => "Préstamo no encontrado"]);
            }
        } elseif(isset($_GET['monto'])){
            $calendario = $_GET['monto'];
            $result_calendario = $prestamoService->generarCalendarioPagos_simple(3000,20,2,fechaInicioStr: '2025-03-17');
            if($result_calendario){
                echo json_encode($result_calendario);
            }else{
                http_response_code(404); // No encontrado
                echo json_encode(["message" => "Calendario no generado"]);
            }

        }
        else {
            $prestamos = $prestamoService->getPrestamos();
            echo json_encode($prestamos);
        }
        break;

    case 'POST':
        // Crear un nuevo préstamo
        $data = json_decode(file_get_contents('php://input'),true);
        $id_prestamo = $prestamoService->createPrestamo($data);
        http_response_code(201); // Creado
        //Cambia el estado de la solicitud a En revision
        //$estadoSolicitud = $PrestamoSolicitud->updateSolicitudEstado($_SESSION["idusuario"],3,$data["id_solicitud"]);
        echo json_encode(["message" => "Préstamo creado", "id_prestamo" => $id_prestamo]);
        break;

    case 'PUT':
        // Actualizar un préstamo existente
        $id_prestamo = $_GET['id_prestamo'];
        $data = json_decode(file_get_contents('php://input'), true);
        $rows = $prestamoService->updatePrestamo($id_prestamo, $data);
        if ($rows > 0) {
            http_response_code(200); // OK
            echo json_encode(["message" => "Préstamo actualizado"]);
        } else {
            http_response_code(404); // No encontrado
            echo json_encode(["message" => "Préstamo no encontrado"]);
        }
        break;

    case 'DELETE':
        // Eliminar un préstamo
        $id_prestamo = $_GET['id_prestamo'];
        $rows = $prestamoService->deletePrestamo($id_prestamo);
        if ($rows > 0) {
            http_response_code(200); // OK
            echo json_encode(["message" => "Préstamo eliminado"]);
        } else {
            http_response_code(404); // No encontrado
            echo json_encode(["message" => "Préstamo no encontrado"]);
        }
        break;

    default:
        http_response_code(405); // Método no permitido
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
?>