<?php
header("Content-Type: application/json");
require_once '../cn.php';
require_once 'fnmora.php'; // Aquí está definida la clase Reportes

// Crear instancia del servicio de reportes
$reporteService = new Reportes($base_de_datos);

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        // Obtener parámetros de la URL
        $id_prestamo = isset($_GET['id_prestamo']) ? intval($_GET['id_prestamo']) : null;
        $codigoCarterafiltro = isset($_GET['codigoCarterafiltro']) ? (int)$_GET['codigoCarterafiltro'] : null;
        $cartera = $_SESSION["carterausuario"] ?? null;

        try {
            // Ejecutar reporte

            $perfilUsuario = $_SESSION["perfilusuario"] ?? 'Usuario';
          
            $resultado = $reporteService->ReportePrestamoMora($id_prestamo, $cartera,$codigoCarterafiltro, $perfilUsuario);

            if ($resultado) {
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
