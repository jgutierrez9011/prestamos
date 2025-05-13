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
        $id_solicitud = $_GET['id_solicitud'] ?? null;
        $fecha = $_GET['fecha'] ?? null;
        $cliente = $_GET['cliente'] ?? null;
    
        if ($id_solicitud || $fecha || $cliente) {
            $resultado = $solicitudBL->getSolicitud($id_solicitud, $fecha, $cliente);
    
            // Si es un array lo devuelves tal cual, si es un solo objeto lo conviertes a array
            if (is_array($resultado) && isset($resultado[0])) {
                echo json_encode($resultado);
            } else {
                echo json_encode($resultado ? [$resultado] : []);
            }
        } else {
            $solicitudes = $solicitudBL->getAllSolicitudes($_SESSION["perfilusuario"], $_SESSION["carterausuario"]);
            echo json_encode($solicitudes);
        }
        break;       
    
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        // Si se envía una acción de cambio de estado (nuevo endpoint)
        if (isset($data['action']) && $data['action'] === 'cambiar_estado') {

            $requiredFields = ['codigo_solicitud', 'estatus'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Falta el campo requerido: $field"]);
                    exit;
                }
            }

            // Primero obtener el estado actual para verificar si necesita cambio
            $solicitudActual = $solicitudBL->getSolicitud($data['codigo_solicitud']);

            if ($solicitudActual['estatus'] == 'Pendiente' || $solicitudActual['estatus'] == 'En revisión') {

                // Llamar al método para actualizar el estado
                $response = $solicitudBL->updateSolicitudEstado(
                $_SESSION["idusuario"],
                $data['estatus'],
                $data['codigo_solicitud']);

                echo json_encode($response);
            }
            
            
        } elseif (isset($data['action']) && $data['action'] === 'promedio_venta') {
            $response = $solicitudBL->calcularPromedioVentas($data['tipo'],$data['buena'],$data['media'],$data['baja']);
            echo json_encode($response);
        } elseif(isset($data['action']) && $data['action'] === 'limite_credito'){
            $response = $solicitudBL->validarMontoCartera($data['descripcion'],$data['monto']);
            echo json_encode($response);
        } elseif(isset($data['action']) && $data['action'] === 'estimacion_costo'){

            $rubro = $data['rubro'] ?? '';
            $ventasMensuales = floatval($data['ventasMensuales'] ?? 0);
            $costoUnitario = isset($data['costoUnitario']) ? floatval($data['costoUnitario']) : null;
            $precioVenta = isset($data['precioVenta']) ? floatval($data['precioVenta']) : null;
            $unidadesProducidas = isset($data['unidadesProducidas']) ? floatval($data['unidadesProducidas']) : null;

            $response = $solicitudBL->calcularCostoVenta($rubro, $ventasMensuales, $costoUnitario, $precioVenta, $unidadesProducidas);
            echo json_encode($response);
        }
        // Si es una creación normal
        else {
            $response = $solicitudBL->createSolicitud($data);
            echo json_encode($response);
        }
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