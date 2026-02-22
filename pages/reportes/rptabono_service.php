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
        // Obtener parámetros de la URL con saneamiento
        $cartera = $_SESSION["carterausuario"] ?? null;
        $carterafiltro = isset($_GET['cartera']) ? trim($_GET['cartera']) : null;
        $fechaInicio = isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : null;
        $fechaFin = isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : null;

        // Determinar tipo de usuario (Administrador o no)
        $tipoUsuario = $_SESSION["perfilusuario"] ?? 'Usuario';

        try {
            // Validar fechas si se proporcionan
            $fechaInicioObj = null;
            $fechaFinObj = null;
            if ($fechaInicio) {
                $fechaInicioObj = DateTime::createFromFormat('Y-m-d', $fechaInicio);
                if (!$fechaInicioObj || $fechaInicioObj->format('Y-m-d') !== $fechaInicio) {
                    http_response_code(400);
                    echo json_encode(["error" => "fecha_inicio inválida, formato YYYY-MM-DD esperado."]);
                    exit;
                }
            }
            if ($fechaFin) {
                $fechaFinObj = DateTime::createFromFormat('Y-m-d', $fechaFin);
                if (!$fechaFinObj || $fechaFinObj->format('Y-m-d') !== $fechaFin) {
                    http_response_code(400);
                    echo json_encode(["error" => "fecha_fin inválida, formato YYYY-MM-DD esperado."]);
                    exit;
                }
            }
            if ($fechaInicioObj && $fechaFinObj && $fechaInicioObj > $fechaFinObj) {
                http_response_code(400);
                echo json_encode(["error" => "fecha_inicio no puede ser posterior a fecha_fin."]);
                exit;
            }

            // Ejecutar reporte de abonos
            $resultado = $reporteService->obtenerReporteAbonos($cartera, $carterafiltro, $tipoUsuario, $fechaInicio, $fechaFin);

            if (!empty($resultado)) {
                echo json_encode($resultado);
            } else {
                http_response_code(204); // Sin contenido
                echo json_encode(["message" => "No se encontraron registros."]);
            }
        } catch (Exception $e) {
            http_response_code(500); // Error interno del servidor
            error_log('rptabono_service error: ' . $e->getMessage());
            echo json_encode(["error" => "Ocurrió un error interno en el servidor."]);
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