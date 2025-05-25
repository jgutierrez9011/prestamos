<?php
header("Content-Type: application/json");
require_once '../cn.php'; // Archivo con la conexión a la base de datos
require_once 'fnrpt_abono.php'; // Archivo que contiene la clase Reportes

// Crear instancia del servicio de reportes
$reporteService = new Rptabono($base_de_datos);

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Obtener parámetros de la URL
        $cartera = $_SESSION["carterausuario"] ?? null;
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        
        // Determinar tipo de usuario (Administrador o no)
        $tipoUsuario = $_SESSION["perfilusuario"];
        

        try {
            // Ejecutar reporte de abonos
            $resultado = $reporteService->obtenerReporteAbonos($cartera, $tipoUsuario, $fechaInicio, $fechaFin);

            if (!empty($resultado)) {
                echo json_encode($resultado);
            } else {
                http_response_code(204); // Sin contenido
                echo json_encode(["message" => "No se encontraron registros."]);
            }
        } catch (Exception $e) {
            http_response_code(500); // Error interno del servidor
            echo json_encode(["error" => $e->getMessage()]);
            exit;
        }
        break;

    case 'POST':
    case 'PUT':
    case 'DELETE':
        http_response_code(405); // Método no permitido
        echo json_encode(["message" => "Método no permitido para este recurso."]);
        break;

    default:
        http_response_code(405); // Método no permitido
        echo json_encode(["message" => "Método HTTP no soportado."]);
        break;
}
?>