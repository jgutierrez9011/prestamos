<?php
// servicio_abono.php

header("Content-Type: application/json; charset=UTF-8");
/*header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");*/

// Incluir la lógica de negocio
require_once 'fnabono.php';
require_once 'solicitud_service.php';

// Obtener la conexión a la base de datos (debes configurar esto)
/*require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();*/
require_once '../cn.php';
try {
    $pdo = $base_de_datos;
    //$solicitudBL = new SolicitudPrestamo($pdo);
    // Instanciar la clase Abono
    $abono = new Abono($pdo);

    $solicitudBL = new SolicitudPrestamo($pdo);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}



// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Procesar la solicitud
try {
    switch ($method) {
        case 'GET':
            // Obtener abonos por préstamo
            if (isset($_GET['cod_prestamo'])) {
                $id_prestamo = $_GET['cod_prestamo'];
                $abonos = $abono->obtenerAbonosPorPrestamo($id_prestamo);
                echo json_encode($abonos);
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(["error" => "Falta el parámetro id_prestamo."]);
            }
            break;

        case 'POST':
            // Crear un nuevo abono
            $data = json_decode(file_get_contents("php://input"),true);
            //print_r($data);
            if (
                isset($data["id_prestamo"],$data["fecha_abono"],$data["monto_abono"])
            ) {
                if ($abono->crearAbono($data["id_prestamo"], $data["fecha_abono"], $data["monto_abono"], $data["es_prorroga"])) {
                    
                    $saldoPrestamo = $abono->obtenerSaldoPorPrestamo($data["id_prestamo"]);

                   // echo $saldoPrestamo["saldo"];

                   //echo "<script>console.log(" . $saldoPrestamo["saldo"] . ");</script>";

                    if($saldoPrestamo["saldo"] <= 0.00){

                        // Primero obtener el estado actual para verificar si necesita cambio
                            $solicitudActual = $solicitudBL->getSolicitud($data["id_solicitud"]);
                            // Solo cambiar a "En revisión" si está en estado "Pendiente" (idestatus=1)
                        
                            //echo "<script>console.log(" . $solicitudActual['estatus']. ");</script>";

                            if ($solicitudActual['estatus'] === 'Aprobada') {

                                $resultado = $solicitudBL->updateSolicitudEstado($_SESSION["idusuario"], 7, $data["id_solicitud"]);
                                //print_r($resultado);
                                //echo "<script>console.log(" . $resultado . ");</script>";

                                if (isset($resultado['error'])) {
                                    // Manejar el error adecuadamente
                                    error_log("Error al actualizar estado: " . $resultado['error']);
                                }

                            }

                    }
                    
                    http_response_code(201); // Created
                    echo json_encode(["message" => "Abono creado correctamente."]);

                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["error" => "No se pudo crear el abono."]);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(["error" => "Datos incompletos."]);
            }
            break;

        case 'PUT':
            // Actualizar un abono existente
            $data = json_decode(file_get_contents("php://input"));
            if (
                !empty($data->id_abono) &&
                !empty($data->fecha_abono) &&
                !empty($data->monto_abonado) &&
                isset($data->es_prorroga)
            ) {
                if ($abono->actualizarAbono($data->id_abono, $data->fecha_abono, $data->monto_abonado, $data->es_prorroga)) {
                    http_response_code(200); // OK
                    echo json_encode(["message" => "Abono actualizado correctamente."]);
                } else {
                    http_response_code(500); // Internal Server Error
                    echo json_encode(["error" => "No se pudo actualizar el abono."]);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(["error" => "Datos incompletos."]);
            }
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"));
            
            if (!empty($data->id_abono)) {
                if (isset($data->accion) && $data->accion === 'anular') {
                    // Eliminar y registrar en historial
                    if ($abono->anularYEliminarAbono($data->id_abono, $data->motivo ?? 'Sin motivo')) {
                        http_response_code(200);
                        echo json_encode(["message" => "Abono anulado y eliminado correctamente."]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["error" => "No se pudo anular el abono."]);
                    }
                } else {
                    // Eliminar simple (no recomendado si usas historial)
                    if ($abono->eliminarAbono($data->id_abono)) {
                        http_response_code(200);
                        echo json_encode(["message" => "Abono eliminado correctamente."]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["error" => "No se pudo eliminar el abono."]);
                    }
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Falta el parámetro id_abono."]);
            }
            break;

        default:
            http_response_code(405); // Method Not Allowed
            echo json_encode(["error" => "Método no permitido."]);
            break;
    }
} catch (Exception $e) {
    // Captura cualquier excepción lanzada por fnabono.php
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => $e->getMessage()]);
}
?>