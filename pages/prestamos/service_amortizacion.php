<?php
header("Content-Type: application/json");
require_once '../cn.php';
require_once 'prestamo_service.php';

// Crear una instancia del servicio
$prestamoService = new PrestamoService($base_de_datos);

// Obtener la solicitud HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Manejar la solicitud según el método HTTP
switch ($method) {
    case 'GET':
        // Obtener un préstamo por ID o listar todos
        echo json_encode(["message" => "Servicio no definido"]);
          
        break;

    case 'POST':
        // Crear un nuevo préstamo
        $data = json_decode(file_get_contents('php://input'),true);
        if (isset($data["id_solicitud"],$data["monto_aprobado"],$data["interes"],$data["fecha_primer_cuota"],$data["plazo"])) {

            $result_calendario = $prestamoService->generarCalendarioPagos_simple($data["monto_aprobado"],$data["interes"],$data["plazo"],$data["fecha_primer_cuota"]);
            if($result_calendario){
                echo json_encode($result_calendario);
            }else{
                http_response_code(404); // No encontrado
                echo json_encode(["message" => "Calendario o tabla de amortizacion no generado"]);
            }

        }else{
            http_response_code(404); // Cliente no encontrado
            echo json_encode(["error" => "No logro procesar el calendario o tabla de amortizacion"]);
        }
        break;

    case 'PUT':
        // Obtener un préstamo por ID o listar todos
        echo json_encode(["message" => "Servicio no definido"]);
        break;

    case 'DELETE':
        // Obtener un préstamo por ID o listar todos
        echo json_encode(["message" => "Servicio no definido"]);
        break;

    default:
        http_response_code(405); // Método no permitido
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
?>